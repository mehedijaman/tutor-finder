<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auth\RoleRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleDashboardRedirectController extends Controller
{
    /**
     * Redirect authenticated users to their role destination.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect('/login');
        }

        return redirect(RoleRedirector::destinationFor($user));
    }
}
