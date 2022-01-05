<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Visanduma\LaravelInvoice\Models\Invoice;

trait InvoiceAble
{
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }
}
