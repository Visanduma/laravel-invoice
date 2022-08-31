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

    protected $guarded = [];
    protected $table = "laravel_invoices";

    protected static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            $model->updateCalculation();
        });
    }

    public static function make()
    {
        return new static();
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

    public function getExtraValue($key)
    {
        return $this->extra()->where('key', $key)->first()->value ?? "";
    }

    public function statusToString()
    {
        return Str::replace("_", " ", $this->status);
    }

    public function updateCalculation()
    {
        $this->update([
            'total' => $this->items()->sum('total') - $this->discount
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
