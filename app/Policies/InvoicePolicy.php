<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $this->isPayer($user, $invoice);
    }

    /**
     * Determine whether the user can initiate payment for this invoice.
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        return $this->isPayer($user, $invoice);
    }

    /**
     * Check whether the user is the payer of this invoice.
     */
    private function isPayer(User $user, Invoice $invoice): bool
    {
        $payerUserId = (int) ($invoice->payer_user_id ?? $invoice->user_id);

        return (int) $user->getKey() === $payerUserId;
    }
}
