<div class="auth-panel">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_20%_50%,rgba(13,115,119,0.18),transparent_55%)]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-24 top-1/2 h-96 w-96 -translate-y-1/2 rounded-full bg-brand-700/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative z-10 flex flex-1 items-center px-10 py-12 lg:px-16 xl:px-20">
        <div class="max-w-xl">
            <x-logo size="lg" variant="full" class="mb-12" />

            <p class="text-sm font-medium uppercase tracking-[0.2em] text-brand-300">{{ config('app.name', 'ResearchHub') }}</p>
            <h1 class="mt-4 font-display text-4xl font-normal leading-[1.15] tracking-tight text-white xl:text-5xl">
                Research &amp; Thesis Management
            </h1>
            <div class="mt-6 h-px w-16 bg-brand-500/60" aria-hidden="true"></div>
            <p class="mt-6 text-lg leading-relaxed text-navy-100/90 xl:text-xl">
                A centralized workspace for proposal submission, supervision, review, and academic record keeping across your institution.
            </p>

            <ul class="mt-12 space-y-4 text-base leading-relaxed text-navy-200">
                <li class="flex items-start gap-4">
                    <span class="mt-2 flex h-2 w-2 shrink-0 rounded-full bg-brand-400" aria-hidden="true"></span>
                    Role-based access for students, supervisors, reviewers, and administrators
                </li>
                <li class="flex items-start gap-4">
                    <span class="mt-2 flex h-2 w-2 shrink-0 rounded-full bg-brand-400" aria-hidden="true"></span>
                    Structured workflows from proposal to final submission
                </li>
                <li class="flex items-start gap-4">
                    <span class="mt-2 flex h-2 w-2 shrink-0 rounded-full bg-brand-400" aria-hidden="true"></span>
                    Audit-ready records and institutional oversight
                </li>
            </ul>
        </div>
    </div>

    <p class="relative z-10 shrink-0 border-t border-white/5 px-10 py-6 text-xs text-navy-400 lg:px-16 xl:px-20">&copy; {{ date('Y') }} {{ config('app.name', 'ResearchHub') }}. Authorized institutional use only.</p>
</div>
