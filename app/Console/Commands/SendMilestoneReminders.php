<?php

namespace App\Console\Commands;

use App\Enums\MilestoneStatus;
use App\Enums\ThesisStatus;
use App\Mail\MilestoneReminderMail;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendMilestoneReminders extends Command
{
    protected $signature = 'milestones:send-reminders';

    protected $description = 'Email students and supervisors about overdue and upcoming milestones';

    public function handle(): int
    {
        $overdue = Milestone::query()
            ->with(['thesis.student', 'thesis.supervisor'])
            ->whereIn('status', MilestoneStatus::openCases())
            ->whereDate('due_date', '<', now()->startOfDay())
            ->whereHas('thesis', fn ($query) => $query->whereIn('status', ThesisStatus::activeCases()))
            ->get();

        $dueSoon = Milestone::query()
            ->with(['thesis.student', 'thesis.supervisor'])
            ->whereIn('status', MilestoneStatus::openCases())
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->whereHas('thesis', fn ($query) => $query->whereIn('status', ThesisStatus::activeCases()))
            ->get();

        if ($overdue->isEmpty() && $dueSoon->isEmpty()) {
            $this->info('No milestone reminders to send.');

            return self::SUCCESS;
        }

        $recipients = collect();

        foreach ($overdue->concat($dueSoon) as $milestone) {
            $recipients->push($milestone->thesis->student_id);
            $recipients->push($milestone->thesis->supervisor_id);
        }

        $sent = 0;

        User::query()
            ->whereIn('id', $recipients->unique()->filter())
            ->where('is_active', true)
            ->each(function (User $user) use ($overdue, $dueSoon, &$sent): void {
                $userOverdue = $this->milestonesForUser($user, $overdue);
                $userDueSoon = $this->milestonesForUser($user, $dueSoon);

                if ($userOverdue->isEmpty() && $userDueSoon->isEmpty()) {
                    return;
                }

                Mail::to($user)->send(new MilestoneReminderMail($user, $userOverdue, $userDueSoon));
                $sent++;
            });

        $this->info("Sent {$sent} milestone reminder(s).");

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Milestone>  $milestones
     * @return Collection<int, Milestone>
     */
    private function milestonesForUser(User $user, Collection $milestones): Collection
    {
        return $milestones->filter(function (Milestone $milestone) use ($user): bool {
            if ($user->isStudent()) {
                return $milestone->thesis->student_id === $user->id;
            }

            if ($user->isSupervisor()) {
                return $milestone->thesis->supervisor_id === $user->id;
            }

            return false;
        })->values();
    }
}
