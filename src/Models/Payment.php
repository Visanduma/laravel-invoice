<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "laravel_invoice_payments";

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}
