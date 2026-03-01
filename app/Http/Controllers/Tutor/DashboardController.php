<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the tutor dashboard.
     */
    public function __invoke(): Response
    {
        return inertia('tutor/Dashboard');
    }
}
