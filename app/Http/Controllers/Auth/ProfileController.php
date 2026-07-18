<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user()->load('department'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'regex:/^01\d{9}$/'],
            'research_interests' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'phone.regex' => 'The phone must be an 11-digit number starting with 01.',
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

        if ($request->hasFile('avatar')) {
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
