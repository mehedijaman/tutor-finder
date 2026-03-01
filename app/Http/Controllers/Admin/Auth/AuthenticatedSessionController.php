<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function create(): Response
    {
        return inertia('admin/auth/Login');
    }

    /**
     * Authenticate an admin user.
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = [
            'email' => strtolower((string) $request->input('email')),
            'password' => (string) $request->input('password'),
            'role' => 'admin',
            'status' => 'active',
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin/dashboard');
    }
}
