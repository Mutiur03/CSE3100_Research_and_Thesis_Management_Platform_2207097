<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use App\Enums\MilestoneTaskStatus;
use Database\Factories\MilestoneTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilestoneTask extends Model
{
    /** @use HasFactory<MilestoneTaskFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'milestone_id',
        'title',
        'description',
        'assigned_to',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MilestoneTaskStatus::class,
            'priority' => \App\Enums\MilestoneTaskPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOwnedByStudent(User $user): bool
    {
        return $this->assigned_to === $user->id;
    }
}
