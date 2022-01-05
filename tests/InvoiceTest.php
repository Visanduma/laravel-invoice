<?php

namespace Visanduma\LaravelInvoice\Tests;
;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Visanduma\LaravelInvoice\Models\Invoice;
use Visanduma\LaravelInvoice\Models\InvoiceItem;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    private $testModel, $invoice;


    public function test_itCanCreateEmptyInvoiceObject()
    {
        $invoice = Invoice::make();
        $this->assertEquals(0, $invoice->getItemCount());
        $this->assertEquals(0, $invoice->getItemsTotal());

    }

    public function test_itCanCreateInvoiceItemObject()
    {
        $item = InvoiceItem::make();
        $item->setPrice(10);
        $item->setQty(1);

        $this->assertEquals(10, $item->totalWithDiscount());
    }

    public function test_itCanApplyDiscountPerItem()
    {
        $item = InvoiceItem::make();
        $item->setPrice(100);
        $item->setQty(1);

        $item->setDiscount(20);
        $this->assertEquals(80, $item->totalWithDiscount());
        $this->assertEquals(100, $item->totalWithoutDiscount());

        $item->setDiscountPercentage(10);
        $this->assertEquals(90, $item->totalWithDiscount());
    }


//    public function test_saveInvoiceToDatabase()
//    {
//        $model = new TestModel();
//        $model->save();
//
//        $invoice = Invoice::make();
//        $invoice->invoiceToName('Lahiru');
//        $invoice->invoiceToAddress('Anuradhapura');
//
//        $model->invoices()->save($invoice);
//
//
//    }


}
