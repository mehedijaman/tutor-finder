<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use Illuminate\Support\Carbon;

class InvoiceNumberGenerator
{
    /**
     * Generate deterministic invoice number from persisted id.
     */
    public function generateFor(Invoice $invoice): string
    {
        $existingInvoiceNo = trim((string) $invoice->invoice_no);

        if ($existingInvoiceNo !== '' && ! str_starts_with($existingInvoiceNo, 'TMP-')) {
            return $existingInvoiceNo;
        }

        $date = $invoice->created_at instanceof Carbon
            ? $invoice->created_at->format('Ymd')
            : now()->format('Ymd');

        return sprintf('INV-%s-%06d', $date, (int) $invoice->getKey());
    }
}
