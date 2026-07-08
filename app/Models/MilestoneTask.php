<?php

namespace App\Models;

use App\Enums\MilestoneTaskPriority;
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
            'priority' => MilestoneTaskPriority::class,
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

    public function isOwnedByStudent(User $user): bool
    {
        return $this->assigned_to === $user->id;
    }

    public function syncStatus(MilestoneTaskStatus $status): void
    {
        $attributes = ['status' => $status];

        if ($status === MilestoneTaskStatus::Completed) {
            $attributes['completed_at'] = now();
        } else {
            $attributes['completed_at'] = null;
        }

        $this->update($attributes);
        $this->milestone->recalculateProgress();
    }
}
