<?php

namespace App\Events;

use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HireConfirmed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  list<int>  $rejectedTutorUserIds
     */
    public function __construct(
        public TuitionJob $tuitionJob,
        public TuitionJobApplication $application,
        public ?int $selectedTutorUserId,
        public array $rejectedTutorUserIds = [],
    ) {}
}
