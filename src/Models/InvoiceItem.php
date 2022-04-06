<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceItemActions;

class InvoiceItem extends Model
{
    use HasFactory;
    use InvoiceItemActions;

    protected $guarded = [];
    protected $table = "laravel_invoice_items";

    public static function make($name = '', $price = 0, $qty = 1)
    {
        return new static($name, $price, $qty);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
