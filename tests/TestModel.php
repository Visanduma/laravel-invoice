<?php


namespace Visanduma\LaravelInvoice\Tests;


use Illuminate\Database\Eloquent\Model;
use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceAble;

class TestModel extends Model
{
    use InvoiceAble;

    protected $guarded = [];
}
