<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProfilePhotoController extends Controller
{
    /**
     * Update the user's profile photo.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        if ($request->hasFile('photo')) {
            $request->user()->addMediaFromRequest('photo')
                ->toMediaCollection('profile_photo');
        }

        return Redirect::back()->with('status', 'profile-photo-updated');
    }

    /**
     * Delete the user's profile photo.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->clearMediaCollection('profile_photo');

        return Redirect::back()->with('status', 'profile-photo-deleted');
    }
}
