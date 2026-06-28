<?php

namespace App\Models;

use App\Enums\MeetingRsvpStatus;
use Database\Factories\MeetingAttendeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperMeetingAttendee
 */
class MeetingAttendee extends Model
{
    /** @use HasFactory<MeetingAttendeeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'meeting_id',
        'user_id',
        'rsvp_status',
        'attended',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rsvp_status' => MeetingRsvpStatus::class,
            'attended' => 'boolean',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
