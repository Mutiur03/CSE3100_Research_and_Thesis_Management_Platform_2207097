<?php

namespace App\Services;

use App\Enums\MilestoneTaskStatus;
use App\Models\Milestone;
use App\Models\MilestoneTask;

class MilestoneProgressService
{
    public function syncTaskStatus(MilestoneTask $task, MilestoneTaskStatus $status): void
    {
        $attributes = ['status' => $status];

        if ($status === MilestoneTaskStatus::Completed) {
            $attributes['completed_at'] = now();
        } else {
            $attributes['completed_at'] = null;
        }

        $task->update($attributes);
        $task->milestone->recalculateProgress();
    }
}
