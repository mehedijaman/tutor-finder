<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating the selected user.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        $impersonator = $request->user();

        if (! $impersonator instanceof User || ! $impersonator->canImpersonate()) {
            abort(403);
        }

        if ($impersonator->isImpersonated()) {
            return redirect()->back()->withErrors([
                'impersonation' => 'Leave current impersonation first.',
            ]);
        }

        if ($impersonator->is($user)) {
            return redirect()->back()->withErrors([
                'impersonation' => 'You cannot impersonate your own account.',
            ]);
        }

        if (! $user->canBeImpersonated()) {
            abort(403);
        }

        if (! $impersonator->impersonate($user)) {
            return redirect()->back()->withErrors([
                'impersonation' => 'Unable to start impersonation right now.',
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', "You are now impersonating {$user->name}.");
    }

    /**
     * Leave the current impersonation and return to admin account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser instanceof User || ! $currentUser->isImpersonated()) {
            abort(403);
        }

        if (! $currentUser->leaveImpersonation()) {
            return redirect()->back()->withErrors([
                'impersonation' => 'Unable to leave impersonation right now.',
            ]);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'You have returned to your admin account.');
    }
}
