<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Database\Factories\MilestoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'completed_at',
        'sort_order',
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
        ];
    }

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === MilestoneStatus::Pending
            && $this->due_date->lt(now()->startOfDay());
    }

    public function isCompletable(): bool
    {
        return $this->status === MilestoneStatus::Pending
            && $this->thesis->isActive();
    }
}
