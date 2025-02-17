<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Visanduma\LaravelInvoice\Helpers\Traits\HasExtraValues;
use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceActions;
use Visanduma\LaravelInvoice\Helpers\Traits\Taxable;

class Invoice extends Model
{
    use HasFactory;
    use InvoiceActions;
    use HasExtraValues;
    use Taxable;

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_SENT = 'SENT';
    const STATUS_CANCELED = 'CANCELED';

    const STATUS_PAID = 'PAID';
    const STATUS_UNPAID = 'UNPAID';
    const STATUS_PARTIALLY_PAID = 'PARTIALLY PAID';
    const STATUS_PARTIALLY_REFUND = 'PARTIALLY REFUND';
    const STATUS_REFUND = 'REFUND';

    const TYPE_INVOICE = 0;
    const TYPE_QUOTE = 1;

    protected $guarded = [
        'invoice_number'
    ];
    protected $table = "laravel_invoices";

    protected $casts = [
        'discount' => 'double',
        'due_amount' => 'double',
        'total' => 'double',
        'paid_amount' => 'double'
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->invoice_date = $model->invoice_date ?? now();
            $model->invoice_number = self::getNextInvoiceNumber();
            $model->status = $model->status ?? self::STATUS_DRAFT;
            $model->paid_status = $model->paid_status ?? self::STATUS_UNPAID;
            $model->created_by = $model->created_by ?? auth()->id();
        });

        self::created(function ($model) {
            $model->updateCalculation();
        });
    }

    public static function make()
    {
        return static::create();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->rootWithChildren();
    }



    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function statusToString()
    {
        return Str::replace("_", " ", $this->status);
    }

    public function updateCalculation()
    {
        // todo improve this calculation
        $total = ($this->getItemsTotal() + $this->totalTaxAmount()) - ($this->discount);

        $this->update([
            'total' => $total,
            'sub_total' => $this->getItemsTotal(),
            'due_amount' => $total - $this->paidAmount()
        ]);
    }


    public function setStatus($status)
    {
        $this->update([
            'status' => $status
        ]);
    }

    public function setPaymentStatus($status)
    {
        $this->update([
            'paid_status' => $status
        ]);
    }

    public function paidAmount()
    {
        return $this->payments()->sum('amount');
    }

    public function user()
    {
        return $this->belongsTo(config('invoice.user_model'), 'created_by');
    }

    public function invoiceable()
    {
        return $this->morphTo('invoiceable');
    }
}
