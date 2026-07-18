<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperComment
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'parent_id',
        'body',
        'is_private',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->latest();
    }

    public function mentions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comment_mentions')->withTimestamps();
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isStudent()) {
            $query->where('is_private', false);
        }
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeTopLevel(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->is_private && $user->isStudent()) {
            return false;
        }

        return true;
    }

    /**
     * @return Collection<int, User>
     */
    public static function mentionableUsers(Thesis $thesis): Collection
    {
        return User::query()
            ->whereIn('id', [$thesis->student_id, $thesis->supervisor_id])
            ->get();
    }

    /**
     * @return list<int>
     */
    public static function parseMentionedUserIds(string $body, Thesis $thesis): array
    {
        $participants = self::mentionableUsers($thesis);
        $mentionedIds = [];

        foreach ($participants as $user) {
            if (self::bodyMentionsUser($body, $user)) {
                $mentionedIds[] = $user->id;
            }
        }

        return array_values(array_unique($mentionedIds));
    }

    public static function store(Thesis $thesis, User $author, string $body, ?int $parentId = null, bool $isPrivate = false): self
    {
        $comment = $thesis->comments()->create([
            'user_id' => $author->id,
            'parent_id' => $parentId,
            'body' => $body,
            'is_private' => $isPrivate,
        ]);

        $mentionIds = self::parseMentionedUserIds($body, $thesis);

        if ($mentionIds !== []) {
            $comment->mentions()->sync($mentionIds);
        }

        return $comment->load(['user', 'mentions', 'replies.user']);
    }

    public static function formatBody(string $body, Thesis $thesis): string
    {
        $escaped = e($body);
        $participants = self::mentionableUsers($thesis);

        foreach ($participants as $user) {
            $chip = '<span class="font-semibold text-navy-700">@'.e($user->name).'</span>';

            foreach (self::mentionPatternsForUser($user) as $pattern) {
                $escaped = preg_replace_callback(
                    $pattern,
                    fn () => $chip,
                    $escaped,
                ) ?? $escaped;
            }
        }

        return nl2br($escaped);
    }

    private static function bodyMentionsUser(string $body, User $user): bool
    {
        foreach (self::mentionPatternsForUser($user) as $pattern) {
            if (preg_match($pattern, $body) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private static function mentionPatternsForUser(User $user): array
    {
        $patterns = [];

        if ($user->email) {
            $patterns[] = '/@'.preg_quote($user->email, '/').'\b/i';
        }

        $name = trim($user->name);

        if ($name !== '') {
            // Natural "@First Last" with spaces (stop at punctuation/end).
            $patterns[] = '/@'.preg_quote($name, '/').'(?=\s|[.,;:!?)]|$)/iu';

            // Compact handle without spaces, e.g. @FirstLast
            $nameHandle = str_replace(' ', '', $name);
            if ($nameHandle !== '' && $nameHandle !== $name) {
                $patterns[] = '/@'.preg_quote($nameHandle, '/').'\b/iu';
            }
        }

        return $patterns;
    }
}
