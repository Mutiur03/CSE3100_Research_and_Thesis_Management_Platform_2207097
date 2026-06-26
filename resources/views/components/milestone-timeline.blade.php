@props(['thesis'])

@if($thesis->milestones->isNotEmpty())
    @php
        $rangeStart = $thesis->started_at->copy()->startOfDay();
        $rangeEnd = $thesis->milestones->max('due_date')?->copy()->addDays(7) ?? now()->addMonth();
        if ($rangeEnd->lte($rangeStart)) {
            $rangeEnd = $rangeStart->copy()->addDays(30);
        }
        $totalDays = max($rangeStart->diffInDays($rangeEnd), 1);
    @endphp

    <div class="card">
        <div class="card-section">
            <h3 class="text-sm font-semibold text-stone-900">Timeline</h3>
            <p class="mt-0.5 text-sm text-stone-500">Milestone schedule from project start to final due date.</p>
        </div>
        <div class="card-body space-y-4">
            <div class="relative h-3 overflow-hidden rounded-full bg-stone-100">
                <div class="absolute inset-y-0 left-0 rounded-full bg-brand-200" style="width: 100%"></div>
            </div>
            <div class="relative min-h-[4.5rem]">
                @foreach($thesis->milestones as $milestone)
                    @php
                        $offsetDays = $rangeStart->diffInDays($milestone->due_date->copy()->startOfDay());
                        $left = min(100, max(0, ($offsetDays / $totalDays) * 100));
                        $barColor = match(true) {
                            $milestone->status === \App\Enums\MilestoneStatus::Completed => 'bg-emerald-500',
                            $milestone->isOverdue() => 'bg-amber-500',
                            $milestone->status === \App\Enums\MilestoneStatus::InProgress => 'bg-indigo-500',
                            default => 'bg-sky-500',
                        };
                    @endphp
                    <div class="absolute top-0 -translate-x-1/2" style="left: {{ $left }}%">
                        <div class="flex flex-col items-center">
                            <div class="h-3 w-3 rounded-full {{ $barColor }} ring-2 ring-white"></div>
                            <p class="mt-2 max-w-[7rem] truncate text-center text-[11px] font-medium text-stone-700" title="{{ $milestone->title }}">{{ $milestone->title }}</p>
                            <p class="text-[10px] text-stone-500">{{ $milestone->due_date->format('M j') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[11px] text-stone-500">
                <span>{{ $rangeStart->format('M j, Y') }}</span>
                <span>{{ $rangeEnd->format('M j, Y') }}</span>
            </div>
        </div>
    </div>
@endif
