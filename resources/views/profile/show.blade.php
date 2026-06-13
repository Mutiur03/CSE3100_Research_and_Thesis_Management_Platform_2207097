@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="page-shell max-w-3xl">
        <header class="page-header">
            <h2 class="page-title">Profile</h2>
            <p class="page-lead">Personal information and research profile.</p>
        </header>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6" id="profile-form">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-section">
                    <h3 class="text-sm font-semibold text-stone-900">Photo</h3>
                </div>
                <div class="card-body flex items-center gap-6">
                    <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Profile photo" class="h-16 w-16 rounded object-cover ring-1 ring-stone-200">
                    <div>
                        <label for="avatar" class="btn-secondary cursor-pointer">Upload photo</label>
                        <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                        <p class="field-hint">JPG, PNG, or WebP. Maximum 2 MB.</p>
                    </div>
                </div>
                @error('avatar')
                    <div class="px-6 pb-4"><p class="field-error">{{ $message }}</p></div>
                @enderror
            </div>

            <div class="card">
                <div class="card-section">
                    <h3 class="text-sm font-semibold text-stone-900">Personal information</h3>
                </div>
                <div class="card-body space-y-5">
                    <div>
                        <label for="name" class="field-label">Full name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="input-field @error('name') input-error @enderror">
                        @error('name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email-display" class="field-label">Email address</label>
                        <input type="email" id="email-display" value="{{ $user->email }}" disabled class="input-field bg-stone-50 text-stone-500">
                        <p class="field-hint">Contact an administrator to change your email.</p>
                    </div>

                    <div>
                        <span class="field-label">Role</span>
                        <div class="mt-1.5">
                            <x-role-badge :role="$user->role" />
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="field-label">Phone number</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="input-field @error('phone') input-error @enderror" placeholder="+880 1XXX-XXXXXX">
                        @error('phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-section">
                    <h3 class="text-sm font-semibold text-stone-900">Research profile</h3>
                </div>
                <div class="card-body space-y-5">
                    <div>
                        <label for="bio" class="field-label">Biography</label>
                        <textarea name="bio" id="bio" rows="4" class="textarea-field @error('bio') input-error @enderror" placeholder="Research background and areas of expertise">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="research_interests" class="field-label">Research interests</label>
                        <input type="text" name="research_interests" id="research_interests" value="{{ old('research_interests', $user->research_interests ? implode(', ', $user->research_interests) : '') }}" class="input-field @error('research_interests') input-error @enderror" placeholder="Machine Learning, NLP, Computer Vision">
                        <p class="field-hint">Separate multiple interests with commas.</p>
                        @error('research_interests')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="profile-submit" class="btn-primary">Save changes</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    @endpush
@endsection
