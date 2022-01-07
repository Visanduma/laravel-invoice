<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Visanduma\LaravelInvoice\Helpers\Services\InvoiceService;
use Visanduma\LaravelInvoice\Models\Invoice;

trait InvoiceAble
{
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function attachInvoice(InvoiceService $invoice): Invoice
    {
        $inv = $this->invoices()->create($invoice->toArray()['invoice']);
        $inv->items()->createMany($invoice->toArray()['items']);
        $inv->extra()->createMany($invoice->toArray()['extra']);

        return $inv;
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
