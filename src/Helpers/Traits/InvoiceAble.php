<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Visanduma\LaravelInvoice\Models\Invoice;

trait InvoiceAble
{
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function attachInvoice(Invoice $invoice)
    {
        return $this->invoices()->save($invoice);
    }

    public function lastInvoice(): Invoice
    {
        return $this->invoices()->latest()->first();
    }

    public function findInvoiceByNumber(string $number): Invoice
    {
        return $this->invoices()->where('invoice_number', $number)->first();
    }

    public function findInvoiceById($invoice_id): Invoice
    {
        return $this->invoices()->where('id', $invoice_id)->first();
    }


}
