<?php

namespace Visanduma\LaravelInvoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Visanduma\LaravelInvoice\LaravelInvoice
 */
class LaravelInvoice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-invoice';
    }
}
