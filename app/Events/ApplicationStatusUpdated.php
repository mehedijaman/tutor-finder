<?php

namespace App\Events;

use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public TuitionJob $tuitionJob,
        public TuitionJobApplication $application,
        public string $status,
    ) {}
}
