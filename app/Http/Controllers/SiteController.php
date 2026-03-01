<?php

namespace App\Http\Controllers;

use Laravel\Fortify\Features;

class SiteController extends Controller
{
    public function index()
    {
        return inertia('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    public function jobs()
    {
        return inertia('JobBoard');
    }

    public function faq()
    {
        return inertia('Faq');
    }

    public function blog()
    {
        return inertia('Blog');
    }

    public function contact()
    {
        return inertia('Contact');
    }

    public function privacy()
    {
        return inertia('PrivacyPolicy');
    }

    public function terms()
    {
        return inertia('TermsOfService');
    }
}
