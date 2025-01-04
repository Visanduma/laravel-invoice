<?php

namespace Visanduma\LaravelInvoice\Models;

use Illuminate\Database\Eloquent\Model;

class LabelMapping extends Model
{
    protected $fillable = ['key', 'label'];
}
