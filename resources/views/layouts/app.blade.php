<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Research & Thesis Management Platform">

    <title>{{ config('app.name', 'ResearchHub') }} — @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|libre-baskerville:400,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-50 font-sans text-stone-800 antialiased">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 hidden w-60 flex-col border-r border-stone-200 bg-white lg:flex">
            <div class="flex h-14 items-center gap-3 border-b border-stone-200 px-5">
                <div class="flex h-8 w-8 items-center justify-center rounded bg-navy-800 text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-stone-900">ResearchHub</p>
                    <p class="truncate text-[11px] text-stone-500">Thesis Management</p>
                </div>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto bg-surface-50 px-3 py-5">
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-stone-400">Main</p>

                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    Dashboard
                </x-nav-link>

                <x-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.*')">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    Profile
                </x-nav-link>

                @if(auth()->user()->isStudent())
                    <x-nav-link href="{{ route('student.proposals.index') }}" :active="request()->routeIs('student.proposals.*')">
                        <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        Proposals
                    </x-nav-link>
                    <x-nav-link href="{{ route('student.theses.index') }}" :active="request()->routeIs('student.theses.*')">
                        <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        Theses
                    </x-nav-link>
                @endif

                @if(auth()->user()->isSupervisor())
                    <x-nav-link href="{{ route('supervisor.proposals.index') }}" :active="request()->routeIs('supervisor.proposals.*')">
                        <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                        Reviews
                    </x-nav-link>
                    <x-nav-link href="{{ route('supervisor.theses.index') }}" :active="request()->routeIs('supervisor.theses.*')">
                        <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        Theses
                    </x-nav-link>
                @endif

                @if(auth()->user()->isAdmin())
                    <p class="mb-2 mt-6 px-3 text-[10px] font-semibold uppercase tracking-widest text-stone-400">Administration</p>
                    <x-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">
                        <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        Users
                    </x-nav-link>
                    <x-nav-link href="{{ route('admin.departments.index') }}" :active="request()->routeIs('admin.departments.*')">
                        <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Zm0 3h.008v.008H17.25v-.008Z" /></svg>
                        Departments
                    </x-nav-link>
                @endif
            </nav>

            <div class="border-t border-stone-200 bg-white p-4">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-9 w-9 rounded object-cover ring-1 ring-stone-200">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-stone-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-stone-500">{{ auth()->user()->role->label() }}</p>
                    </div>
                </div>
                <x-logout-button class="mt-3 w-full rounded border border-stone-200 px-3 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50" label="Sign out" />
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col lg:pl-60">
            <header class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-stone-200 bg-white px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" type="button" class="rounded p-2 text-stone-500 hover:bg-stone-100 lg:hidden" aria-label="Open navigation">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-wide text-stone-400">Workspace</p>
                        <h1 class="text-sm font-semibold text-stone-800">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" data-dropdown-toggle class="flex items-center gap-2 rounded border border-stone-200 bg-white py-1.5 pl-1.5 pr-2.5 text-sm hover:bg-stone-50">
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-7 w-7 rounded object-cover">
                        <span class="hidden font-medium text-stone-700 sm:inline">{{ auth()->user()->name }}</span>
                        <svg class="h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div class="absolute right-0 z-50 mt-1 hidden w-48 rounded border border-stone-200 bg-white py-1 shadow-lg dropdown-open:block">
                        <a wire:navigate.hover href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">Profile settings</a>
                        <hr class="my-1 border-stone-100">
                        <x-logout-button />
                    </div>
                </div>
            </header>

            @if(session('success') || session('error') || session('warning'))
                <div class="space-y-2 border-b border-stone-200 bg-stone-50 px-4 py-3 sm:px-6 lg:px-8">
                    @if(session('success'))
                        <x-alert type="success" :message="session('success')" />
                    @endif
                    @if(session('error'))
                        <x-alert type="error" :message="session('error')" />
                    @endif
                    @if(session('warning'))
                        <x-alert type="warning" :message="session('warning')" />
                    @endif
                </div>
            @endif

            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('click', function (e) {
            const dropdownToggle = e.target.closest('[data-dropdown-toggle]');
            if (dropdownToggle) {
                dropdownToggle.parentElement?.classList.toggle('dropdown-open');
                return;
            }

            if (e.target.closest('#sidebar-toggle')) {
                const sidebar = document.getElementById('sidebar');
                sidebar?.classList.toggle('hidden');
                sidebar?.classList.toggle('flex');
                return;
            }

            document.querySelectorAll('.dropdown-open').forEach(function (dropdown) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('dropdown-open');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
