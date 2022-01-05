<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Contracts\InvoiceItemContract;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "module_invoice_items";

    public static function make()
    {
        return new InvoiceItemContract();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
