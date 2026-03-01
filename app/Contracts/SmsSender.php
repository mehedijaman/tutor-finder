<?php

namespace App\Contracts;

interface SmsSender
{
    /**
     * Send an SMS message to a destination phone number.
     */
    public function send(string $phone, string $message): void;
}
