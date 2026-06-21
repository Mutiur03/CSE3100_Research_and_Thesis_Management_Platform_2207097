@extends('layouts.app')

@section('title', 'New Proposal')

@section('content')
    <div class="page-shell">
        <header class="page-header">
            <h2 class="page-title">New proposal</h2>
            <p class="page-lead">Save a draft first. You can submit it to your supervisor when you are ready.</p>
        </header>

        @include('student.proposals._form', [
            'action' => route('student.proposals.store'),
            'method' => 'POST',
            'proposal' => null,
            'supervisors' => $supervisors,
        ])
    </div>
@endsection
