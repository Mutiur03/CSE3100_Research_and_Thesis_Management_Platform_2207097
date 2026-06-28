<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Support\Collection;

class CommentService
{
    /**
     * @return Collection<int, User>
     */
    public function mentionableUsers(Thesis $thesis): Collection
    {
        return User::query()
            ->whereIn('id', [$thesis->student_id, $thesis->supervisor_id])
            ->get();
    }

    /**
     * @return list<int>
     */
    public function parseMentionedUserIds(string $body, Thesis $thesis): array
    {
        $participants = $this->mentionableUsers($thesis);
        $mentionedIds = [];

        foreach ($participants as $user) {
            if ($this->bodyMentionsUser($body, $user)) {
                $mentionedIds[] = $user->id;
            }
        }

        return array_values(array_unique($mentionedIds));
    }

    public function store(Thesis $thesis, User $author, string $body, ?int $parentId = null, bool $isPrivate = false): Comment
    {
        $comment = $thesis->comments()->create([
            'user_id' => $author->id,
            'parent_id' => $parentId,
            'body' => $body,
            'is_private' => $isPrivate,
        ]);

        $mentionIds = $this->parseMentionedUserIds($body, $thesis);

        if ($mentionIds !== []) {
            $comment->mentions()->sync($mentionIds);
        }

        return $comment->load(['user', 'mentions', 'replies.user']);
    }

    public function formatBody(string $body, Thesis $thesis): string
    {
        $escaped = e($body);
        $participants = $this->mentionableUsers($thesis);

        foreach ($participants as $user) {
            $patterns = $this->mentionPatternsForUser($user);

            foreach ($patterns as $pattern) {
                $escaped = preg_replace(
                    $pattern,
                    '<span class="font-semibold text-navy-700">$0</span>',
                    $escaped,
                ) ?? $escaped;
            }
        }

        return nl2br($escaped);
    }

    private function bodyMentionsUser(string $body, User $user): bool
    {
        foreach ($this->mentionPatternsForUser($user) as $pattern) {
            if (preg_match($pattern, $body) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function mentionPatternsForUser(User $user): array
    {
        $patterns = [];

        if ($user->email) {
            $patterns[] = '/@'.preg_quote($user->email, '/').'/';
        }

        $nameHandle = str_replace(' ', '', $user->name);

        if ($nameHandle !== '') {
            $patterns[] = '/@'.preg_quote($nameHandle, '/').'/';
        }

        return $patterns;
    }
}
