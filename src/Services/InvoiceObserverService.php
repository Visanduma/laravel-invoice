<?php

namespace Visanduma\LaravelInvoice\Services;

use Visanduma\LaravelInvoice\Models\Invoice;

class InvoiceObserverService
{
    public static function register($observerClass)
    {
        Invoice::observe($observerClass);
    }
}
