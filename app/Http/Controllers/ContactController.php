<?php

namespace App\Http\Controllers;

use App\Enums\ContactMessageStatus;
use App\Http\Requests\ContactMessageStoreRequest;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Response;
use Laravel\Fortify\Features;

class ContactController extends Controller
{
    /**
     * Display the public contact page.
     */
    public function index(): Response
    {
        $siteSettings = SiteSetting::current()->toAdminPayload();

        return inertia('Contact', [
            'canRegister' => Features::enabled(Features::registration()),
            'contactDetails' => [
                'phones' => $siteSettings['phone_numbers'] ?? [],
                'emails' => $siteSettings['emails'] ?? [],
                'addresses' => $siteSettings['addresses'] ?? [],
                'social_details' => $siteSettings['social_details'] ?? [],
            ],
        ]);
    }

    /**
     * Store an incoming contact message.
     */
    public function store(ContactMessageStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ContactMessage::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => ContactMessageStatus::Open,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        return back()->with('success', 'Thank you for reaching out. We will contact you shortly.');
    }
}
