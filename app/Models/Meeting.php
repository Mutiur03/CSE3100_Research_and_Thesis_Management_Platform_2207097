<?php

namespace App\Models;

use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperMeeting
 */
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'thesis_id',
        'title',
        'description',
        'type',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_link',
        'agenda',
        'minutes',
        'status',
        'organized_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MeetingType::class,
            'status' => MeetingStatus::class,
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organized_by');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function endsAt(): \Illuminate\Support\Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public function isUpcoming(): bool
    {
        return $this->status === MeetingStatus::Scheduled
            && $this->scheduled_at->isFuture();
    }
}
