<x-mail::message>
# Milestone reminder

Hello {{ $recipient->name }},

Here is your milestone summary for **{{ config('app.name') }}**.

@if($overdue->isNotEmpty())
## Overdue milestones

@foreach($overdue as $milestone)
- **{{ $milestone->title }}** — due {{ $milestone->due_date->format('M j, Y') }} ({{ $milestone->thesis->title }})
@endforeach
@endif

@if($dueSoon->isNotEmpty())
## Due within 3 days

@foreach($dueSoon as $milestone)
- **{{ $milestone->title }}** — due {{ $milestone->due_date->format('M j, Y') }} ({{ $milestone->thesis->title }})
@endforeach
@endif

<x-mail::button :url="route('dashboard')">
Open dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
