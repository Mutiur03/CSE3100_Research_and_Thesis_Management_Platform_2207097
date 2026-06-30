@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-shell max-w-3xl">
        <header class="page-header flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="page-title">Notifications</h2>
                <p class="page-lead">Updates about proposals, documents, meetings, and comments.</p>
            </div>

            @if(auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Mark all as read</button>
                </form>
            @endif
        </header>

        <div class="card divide-y divide-stone-100">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                @endphp
                <div class="flex items-start gap-4 px-5 py-4 {{ $isUnread ? 'bg-navy-50/40' : '' }}">
                    <div class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $isUnread ? 'bg-navy-700' : 'bg-transparent' }}"></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-semibold text-stone-900">{{ $data['title'] ?? 'Notification' }}</p>
                            @if(!empty($data['category']))
                                <span class="rounded bg-stone-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-stone-500">
                                    {{ str_replace('_', ' ', $data['category']) }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-stone-600">{{ $data['message'] ?? '' }}</p>
                        <p class="mt-2 text-xs text-stone-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col gap-2">
                        @if(!empty($data['action_url']))
                            <a href="{{ route('notifications.read', $notification->id) }}" class="btn-secondary text-xs">
                                {{ $isUnread ? 'View' : 'Open' }}
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-stone-700">No notifications yet</p>
                    <p class="mt-1 text-sm text-stone-500">Activity on your proposals and theses will appear here.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
