@extends('layouts.app')

@section('title', 'Edit Proposal')

@section('content')
    <div class="page-shell">
        <header class="page-header">
            <h2 class="page-title">Edit proposal</h2>
            <p class="page-lead">Update your draft or revised proposal before resubmitting.</p>
        </header>

        @include('student.proposals._form', [
            'action' => route('student.proposals.update', $proposal),
            'method' => 'PUT',
            'proposal' => $proposal,
            'supervisors' => $supervisors,
        ])
    </div>
@endsection
