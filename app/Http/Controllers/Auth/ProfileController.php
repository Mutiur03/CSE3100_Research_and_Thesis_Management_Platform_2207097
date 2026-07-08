<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the profile edit form.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user()->load('department'),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'research_interests' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.max' => 'The avatar image must not exceed 2MB.',
            'avatar.image' => 'The avatar must be a valid image file.',
        ]);

        $user = $request->user();

        $data = [
            'name' => $request->name,
            'bio' => $request->bio,
            'phone' => $request->phone,
            'research_interests' => $this->parsedResearchInterests($request),
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store(
                'avatars/'.$user->id,
                'public'
            );

            $data['avatar'] = $path;
        }

        $user->update($data);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Parse research interests from comma-separated string to array.
     *
     * @return array<string>|null
     */
    private function parsedResearchInterests(Request $request): ?array
    {
        $raw = $request->input('research_interests');

        if (empty($raw)) {
            return null;
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $raw))
        ));
    }
}
