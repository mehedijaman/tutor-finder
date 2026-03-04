<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin
            || $user->can('payment-view');
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->role === UserRole::Admin || $user->can('payment-view')) {
            return true;
        }

        return $this->isPaymentOwner($user, $payment);
    }

    /**
     * Determine whether the user can create payments.
     * Payments are created automatically through gateways.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the payment.
     * Only admins can update payment status.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $user->role === UserRole::Admin
            || $user->can('payment-update');
    }

    /**
     * Determine whether the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $user->role === UserRole::Admin
            || $user->can('payment-delete');
    }

    /**
     * Determine whether the user can restore the payment.
     */
    public function restore(User $user, Payment $payment): bool
    {
        return $user->role === UserRole::Admin
            || $user->can('payment-restore');
    }

    /**
     * Determine whether the user can permanently delete the payment.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->role === UserRole::Admin
            && $user->can('payment-force-delete');
    }

    /**
     * Determine whether the user can download a receipt for this payment.
     */
    public function downloadReceipt(User $user, Payment $payment): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $this->isPaymentOwner($user, $payment);
    }

    /**
     * Check whether the user owns this payment via the invoice.
     */
    private function isPaymentOwner(User $user, Payment $payment): bool
    {
        $invoice = $payment->invoice;

        if (! $invoice) {
            return false;
        }

        $payerUserId = (int) ($invoice->payer_user_id ?? $invoice->user_id);

        return (int) $user->getKey() === $payerUserId;
    }
}
