<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Contracts\InvoiceContract;


class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "module_invoices";

    public static function make()
    {
        return new InvoiceContract();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function addPayment($amount, $method = 'CASH')
    {
        $this->payments()->create([
            'method' => $method,
            'amount' => $amount,
            'payment_date' => now()
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

    public function extraValue($key)
    {
        return $this->extra->where('key', $key)->first()->value ?? "";
    }


}
