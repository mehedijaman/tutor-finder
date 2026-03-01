<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the guardian dashboard.
     */
    public function __invoke(): Response
    {
        return inertia('guardian/Dashboard');
    }
}
