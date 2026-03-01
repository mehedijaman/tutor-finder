<?php

namespace App\Jobs;

use App\Contracts\SmsSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOtpSmsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phone,
        public string $message,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SmsSender $smsSender): void
    {
        $smsSender->send($this->phone, $this->message);
    }
}
