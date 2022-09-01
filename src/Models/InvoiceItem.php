<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceItemActions;

class InvoiceItem extends Model
{
    use HasFactory;
    use InvoiceItemActions;

    const DISCOUNT_FLAT = 0;
    const DISCOUNT_PERCENTAGE = 1;

    protected $guarded = [];
    protected $table = "laravel_invoice_items";

    protected $casts = [
        'discount' => 'double',
        'discount_value' => 'double',
        'total' => 'double',
        'price' => 'double',
        'qty' => 'int',
    ];


    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->total = ($model['price'] * $model['qty']) - $model['discount'];
        });

        self::updating(function ($model) {
            $model->total = ($model['price'] * $model['qty']) - $model['discount'];
        });

        self::created(function ($model) {
            $model->invoice->updateCalculation();
        });
    }

    public static function make($name = '', $price = 0, $qty = 1)
    {
        return new static($name, $price, $qty);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
