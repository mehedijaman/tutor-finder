<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:500'],
            'keys.auth' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
        );

        return response()->json(['success' => true]);
    }

    /**
     * Delete a push subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        $request->user()->deletePushSubscription($validated['endpoint']);

        return response()->json(['success' => true]);
    }
}
