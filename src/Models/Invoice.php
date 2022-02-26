<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Services\InvoiceService;
use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceActions;


class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "laravel_invoices";

    public static function make()
    {
        return new InvoiceService();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function addPayment($amount, $method = 'CASH')
    {
        
        if($amount != 0){
            $this->payments()->create([
                'method' => $method,
                'amount' => $amount,
                'payment_date' => now()
            ]);

            $this->decrement('due_amount', $amount);
            $this->paid_status = "PAID";
            $this->save();
        }
        
        
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

    public function paidAmount()
    {
        return $this->payments()->sum('amount');
    }

    public function dueAmount()
    {
        return $this->total - $this->payments()->sum('amount');
    }


}
