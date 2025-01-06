<?php

namespace Visanduma\LaravelInvoice\Observers;

use Visanduma\LaravelInvoice\Models\Invoice;

class InvoiceObserver
{
    public function updated(Invoice $invoice)
    {
        // if ($invoice->isDirty('status') && $invoice->status === 'issued') {
        //     foreach ($invoice->items as $item) {
        //         $item->handleItemAction('reduceStock');
        //         $item->handleItemAction('logAction', ['action' => 'invoice issued']);
        //     }
        // }
    }
}
