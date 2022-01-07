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


    public function test_saveInvoiceToDatabase()
    {
        $model = new TestModel();
        $model->save();

        $invoice = Invoice::make();
        $invoice->invoiceToName('Lahiru');
        $invoice->invoiceToAddress('Anuradhapura');

        $model->attachInvoice($invoice);

        $this->assertCount(1, Invoice::all());

    }

    public function test_saveItemsWithInvoice()
    {
        $model = new TestModel();
        $model->save();

        $invoice = Invoice::make();
        $invoice->invoiceToName('Lahiru');
        $invoice->invoiceToAddress('Anuradhapura');

        $invoice->addItems([
            InvoiceItem::make()->setName('Product one')->setPrice(100)->setQty(2),
            InvoiceItem::make()->setName('Product two')->setPrice(100)->setQty(2),
        ]);

        $model->attachInvoice($invoice);

        $this->assertCount(2, InvoiceItem::all());
    }

    public function test_saveInvoiceExtra()
    {
        $model = new TestModel();
        $model->save();

        $invoice = Invoice::make();
        $invoice->invoiceToName('Lahiru');
        $invoice->invoiceToAddress('Anuradhapura');

        $invoice->setExtraValue('fax', '012542856625');

        $model->attachInvoice($invoice);

        $this->assertEquals('012542856625', $model->lastInvoice()->extraValue('fax'));
    }


    public function test_ableToFindInvoices()
    {
        $model = new TestModel();
        $model->save();

        $invoice = Invoice::make();
        $invoice->invoiceToName('Lahiru');
        $invoice->invoiceToAddress('Anuradhapura');
        $invoice->setInvoiceNumber("XA12345");

        $inv = $model->attachInvoice($invoice);

        // find by invoice number
        $this->assertNotNull($model->findInvoiceByNumber("XA12345"));
        // find by invoice ID
        $this->assertNotNull($model->findInvoiceById($inv->id));

    }


}
