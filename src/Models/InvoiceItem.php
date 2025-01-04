<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Traits\HasExtraValues;
use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceItemActions;

class InvoiceItem extends Model
{
    use HasFactory;
    use InvoiceItemActions;
    use HasExtraValues;

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
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];


    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->total = ($model['price'] * $model['qty']) - $model['discount'];

            if ($model->invoiceItemable && method_exists($model->invoiceItemable, 'adjustItemTotal')) {
                $model->total = $model->invoiceItemable->adjustItemTotal($model);
            }
        });

        self::updating(function ($model) {
            $model->total = ($model['price'] * $model['qty']) - $model['discount'];

            if ($model->invoiceItemable && method_exists($model->invoiceItemable, 'adjustItemTotal')) {
                $model->total = $model->invoiceItemable->adjustItemTotal($model);
            }
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


    public function invoiceItemable()
    {
        return $this->morphTo();
    }
    public function handleItemAction($action, $params = [])
    {
        if ($this->invoiceItemable && method_exists($this->invoiceItemable, $action)) {
            // Pass the current InvoiceItem and any additional parameters to the method
            $this->invoiceItemable->{$action}($this, $params);
        }
    }
}
