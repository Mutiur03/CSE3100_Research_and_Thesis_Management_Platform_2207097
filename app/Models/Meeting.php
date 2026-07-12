<?php

namespace App\Models;

use App\Enums\MeetingFormat;
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
        'format',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_link',
        'google_event_id',
        'google_html_link',
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
            'format' => MeetingFormat::class,
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
}
