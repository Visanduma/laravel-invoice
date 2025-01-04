<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceExtra extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'laravel_invoice_extras';

    protected $casts = ['value' => 'json'];

    public function labelMapping()
    {
        return $this->belongsTo(LabelMapping::class, 'key', 'key');
    }

    public function getLabelAttribute()
    {
        return $this->labelMapping->label ?? null;
    }
}
