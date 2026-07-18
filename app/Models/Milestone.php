<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use App\Enums\MilestoneTaskStatus;
use App\Enums\ThesisStatus;
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
        $wasCompleted = $this->status === MilestoneStatus::Completed;

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

        if ($totalTasks > 0 && in_array($this->status, MilestoneStatus::openCases(), true)) {
            if ($progress > 0 && $this->status === MilestoneStatus::Pending) {
                $attributes['status'] = MilestoneStatus::InProgress;
            }
        }

        if ($wasCompleted && $totalTasks > 0 && $progress < 100) {
            $attributes['status'] = MilestoneStatus::InProgress;
            $attributes['completed_at'] = null;
        }

        $this->update($attributes);
        $this->refresh();

        if ($totalTasks > 0 && $progress === 100 && $this->isCompletable()) {
            $this->markCompleted();

            return;
        }

        if ($wasCompleted && $this->status !== MilestoneStatus::Completed) {
            $this->reopenThesisIfNeeded();
        }
    }

    public function markCompleted(): void
    {
        if ($this->status === MilestoneStatus::Completed) {
            return;
        }

        $this->update([
            'status' => MilestoneStatus::Completed,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        $this->tryCompleteDependents();
        $this->completeThesisIfAllMilestonesDone();
    }

    protected function completeThesisIfAllMilestonesDone(): void
    {
        $thesis = $this->thesis;

        if (! $thesis->isActive()) {
            return;
        }

        if ($thesis->milestones()->exists()
            && $thesis->milestones()->where('status', '!=', MilestoneStatus::Completed)->doesntExist()) {
            $thesis->update([
                'status' => ThesisStatus::Completed,
                'completed_at' => now(),
            ]);
        }
    }

    protected function reopenThesisIfNeeded(): void
    {
        $thesis = $this->thesis;

        if ($thesis->status !== ThesisStatus::Completed) {
            return;
        }

        $thesis->update([
            'status' => ThesisStatus::Active,
            'completed_at' => null,
        ]);
    }

    protected function tryCompleteDependents(): void
    {
        self::query()
            ->where('depends_on_id', $this->id)
            ->whereIn('status', MilestoneStatus::openCases())
            ->get()
            ->each(function (self $dependent): void {
                if ($dependent->tasks()->exists()
                    && $dependent->incompleteTasks()->doesntExist()
                    && $dependent->isCompletable()) {
                    $dependent->markCompleted();
                }
            });
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
