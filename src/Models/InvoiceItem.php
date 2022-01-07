<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Services\InvoiceItemService;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "laravel_invoice_items";

    public static function make()
    {
        return new InvoiceItemService();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
