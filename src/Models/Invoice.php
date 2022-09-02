<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceActions;


class Invoice extends Model
{
    use HasFactory;
    use InvoiceActions;

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_SENT = 'SENT';

    const STATUS_PAID = 'PAID';
    const STATUS_UNPAID = 'UNPAID';

    protected $guarded = [
        'invoice_number',
        'discount_value',
        'discount_type'
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
        return $this->hasMany(InvoiceItem::class);
    }

    public function addPayment($amount,  $note = null,$method = 'CASH')
    {
        if($amount == 0){
            return;
        }
        $this->payments()->create([
            'method' => $method,
            'amount' => $amount,
            'payment_date' => now(),
            'note' => $note
        ]);

        $this->decrement('due_amount', $amount);
        $this->paid_status = self::STATUS_PAID;
        $this->save();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function extra()
    {
        return $this->hasMany(InvoiceExtra::class);
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

}
