<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use App\Enums\MilestoneTaskStatus;
use Database\Factories\MilestoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperMilestone
 */
class Milestone extends Model
{
    /** @use HasFactory<MilestoneFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'thesis_id',
        'title',
        'description',
        'due_date',
        'status',
        'progress_percentage',
        'completed_at',
        'sort_order',
        'depends_on_id',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MilestoneStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'progress_percentage' => 'integer',
        ];
    }

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(self::class, 'depends_on_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(self::class, 'depends_on_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MilestoneTask::class)->orderBy('id');
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, MilestoneStatus::openCases(), true)
            && $this->due_date->lt(now()->startOfDay());
    }

    public function isDependencyMet(): bool
    {
        if ($this->depends_on_id === null) {
            return true;
        }

        $dependency = $this->relationLoaded('dependency')
            ? $this->dependency
            : $this->dependency()->first();

        return $dependency?->status === MilestoneStatus::Completed;
    }

    public function isCompletable(): bool
    {
        return in_array($this->status, MilestoneStatus::openCases(), true)
            && $this->thesis->isActive()
            && $this->isDependencyMet()
            && $this->incompleteTasks()->doesntExist();
    }

    public function incompleteTasks(): HasMany
    {
        return $this->tasks()->where('status', '!=', MilestoneTaskStatus::Completed);
    }

    public function recalculateProgress(): void
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            $progress = match ($this->status) {
                MilestoneStatus::Completed => 100,
                MilestoneStatus::InProgress => 50,
                default => 0,
            };
        } else {
            $completed = $this->tasks()->where('status', MilestoneTaskStatus::Completed)->count();
            $progress = (int) round(($completed / $totalTasks) * 100);
        }

        $attributes = ['progress_percentage' => $progress];

        if ($totalTasks > 0 && $progress > 0 && $progress < 100 && $this->status === MilestoneStatus::Pending) {
            $attributes['status'] = MilestoneStatus::InProgress;
        }

        if ($progress === 100 && $totalTasks > 0 && $this->status === MilestoneStatus::Pending) {
            $attributes['status'] = MilestoneStatus::InProgress;
        }

        $this->update($attributes);
    }

    public static function wouldCreateDependencyCycle(int $milestoneId, ?int $dependsOnId): bool
    {
        if ($dependsOnId === null) {
            return false;
        }

        if ($dependsOnId === $milestoneId) {
            return true;
        }

        $visited = [];
        $currentId = $dependsOnId;

        while ($currentId !== null) {
            if ($currentId === $milestoneId) {
                return true;
            }

            if (in_array($currentId, $visited, true)) {
                return true;
            }

            $visited[] = $currentId;
            $currentId = self::query()->whereKey($currentId)->value('depends_on_id');
        }

        return false;
    }
}
