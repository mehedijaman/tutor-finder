<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Invoice $invoice,
        public string $event,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = new MailMessage;

        return match ($this->event) {
            'payment.success' => $mail
                ->subject('Payment Successful - Invoice #'.$this->invoice->invoice_no)
                ->greeting('Hello '.$notifiable->name.'!')
                ->line('Your payment has been successfully processed.')
                ->line('Invoice: #'.$this->invoice->invoice_no)
                ->line('Amount: '.$this->invoice->formatted_amount)
                ->action('View Invoice', $this->getInvoiceUrl())
                ->line('Thank you for your payment!'),

            'payment.failed' => $mail
                ->subject('Payment Failed - Invoice #'.$this->invoice->invoice_no)
                ->greeting('Hello '.$notifiable->name.',')
                ->line('We were unable to process your payment.')
                ->line('Invoice: #'.$this->invoice->invoice_no)
                ->line('Amount: '.$this->invoice->formatted_amount)
                ->action('Try Again', $this->getInvoiceUrl())
                ->line('Please try again or contact support if the issue persists.'),

            'invoice.created' => $mail
                ->subject('New Invoice #'.$this->invoice->invoice_no)
                ->greeting('Hello '.$notifiable->name.',')
                ->line('A new invoice has been generated for you.')
                ->line('Invoice: #'.$this->invoice->invoice_no)
                ->line('Amount: '.$this->invoice->formatted_amount)
                ->line('Due: '.($this->invoice->due_at?->format('M d, Y') ?? 'Upon receipt'))
                ->action('Pay Now', $this->getInvoiceUrl())
                ->line('Please complete your payment to continue.'),

            'refund.approved' => $mail
                ->subject('Refund Approved - Invoice #'.$this->invoice->invoice_no)
                ->greeting('Hello '.$notifiable->name.',')
                ->line('Your refund request has been approved.')
                ->line('Invoice: #'.$this->invoice->invoice_no)
                ->line('Refund Amount: '.$this->invoice->formatted_amount)
                ->action('View Details', $this->getInvoiceUrl())
                ->line('The refund will be processed within 3-5 business days.'),

            default => $mail
                ->subject('Invoice Update - #'.$this->invoice->invoice_no)
                ->greeting('Hello '.$notifiable->name.',')
                ->line('Your invoice #'.$this->invoice->invoice_no.' has been updated.')
                ->line('Status: '.$this->invoice->status->label())
                ->action('View Invoice', $this->getInvoiceUrl()),
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'url' => $this->getInvoiceUrl(),
            'meta' => [
                'invoice_id' => $this->invoice->id,
                'invoice_no' => $this->invoice->invoice_no,
                'amount' => $this->invoice->amount,
                'currency' => $this->invoice->currency,
                'status' => $this->invoice->status->value,
            ],
        ];
    }

    /**
     * Get the notification title based on event.
     */
    protected function getTitle(): string
    {
        return match ($this->event) {
            'payment.success' => 'Payment Successful',
            'payment.failed' => 'Payment Failed',
            'invoice.created' => 'New Invoice',
            'refund.approved' => 'Refund Approved',
            default => 'Invoice Update',
        };
    }

    /**
     * Get the notification message based on event.
     */
    protected function getMessage(): string
    {
        return match ($this->event) {
            'payment.success' => "Your payment of {$this->invoice->formatted_amount} for invoice #{$this->invoice->invoice_no} was successful.",
            'payment.failed' => "Your payment for invoice #{$this->invoice->invoice_no} failed. Please try again.",
            'invoice.created' => "Invoice #{$this->invoice->invoice_no} for {$this->invoice->formatted_amount} has been generated.",
            'refund.approved' => "Your refund of {$this->invoice->formatted_amount} for invoice #{$this->invoice->invoice_no} has been approved.",
            default => "Invoice #{$this->invoice->invoice_no} status: {$this->invoice->status->label()}",
        };
    }

    /**
     * Get the URL to view the invoice.
     */
    protected function getInvoiceUrl(): string
    {
        $user = $this->invoice->payer;

        if (! $user) {
            return url('/');
        }

        return match ($user->role->value ?? $user->role) {
            'tutor' => route('tutor.finance.invoices'),
            'guardian' => route('guardian.finance.invoices'),
            'admin' => route('admin.finance.invoices.index'),
            default => url('/'),
        };
    }
}
