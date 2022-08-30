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
        $this->paid_status = "PAID";
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

    public function paidAmount(): float
    {
        return $this->payments()->sum('amount');
    }

    public static function getNextInvoiceNumber($prefix = "INV")
    {
        $next_id= static::max('id') + 1;
        return $prefix.Str::padLeft($next_id,6,"0");
    }

    public function statusToString()
    {
        return Str::replace("_", " ", $this->status);
    }

    public function updateCalculation()
    {
        $this->total = $this->total();
        $this->save();
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
