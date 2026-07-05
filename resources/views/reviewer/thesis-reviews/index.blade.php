@extends('layouts.app')

@section('title', 'Assigned Reviews')

@section('content')
    <div class="page-shell">
        <header class="page-header">
            <h2 class="page-title">Assigned reviews</h2>
            <p class="page-lead">Thesis projects assigned to you for external evaluation.</p>
        </header>

        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('reviewer.reviews.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="w-full sm:w-52">
                        <label for="status" class="field-label">Status</label>
                        <select name="status" id="status" class="select-field">
                            <option value="">Open reviews</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ $statusFilter === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">Apply</button>
                        @if($statusFilter)
                            <a wire:navigate.hover href="{{ route('reviewer.reviews.index') }}" class="btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-3">Thesis</th>
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Supervisor</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Assigned</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($reviews as $review)
                            <tr class="hover:bg-stone-50/80">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-stone-800">{{ $review->thesis->title }}</p>
                                </td>
                                <td class="px-6 py-4 text-stone-600">
                                    <p class="font-medium text-stone-800">{{ $review->thesis->student->name }}</p>
                                </td>
                                <td class="px-6 py-4 text-stone-600">{{ $review->thesis->supervisor->name }}</td>
                                <td class="px-6 py-4">
                                    <x-thesis-review-status-badge :status="$review->status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-stone-500">
                                    {{ $review->assigned_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a wire:navigate.hover href="{{ route('reviewer.reviews.show', $review) }}" class="btn-secondary btn-sm">
                                        {{ $review->status === \App\Enums\ThesisReviewStatus::Submitted ? 'View' : 'Review' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-stone-500">
                                    No thesis reviews assigned to you yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($reviews->hasPages())
            <div class="mt-6">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
