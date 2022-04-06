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

    private function make_invoice($number = 'XA12345')
    {
        $model = new TestModel();
        $model->save();

        $invoice = [
            'invoice_date' => now(),
            'tag' => 'prime',
            'due_data' => now()->addDay(),
            'invoice_number' => $number,
            'status' => 'CREATED',
            'paid_status' => 'PENDING',
            'note' => '',
            'discount' => 0,
            'discount_value' => 150,
            'discount_type' => 1,
            'sub_total' => 5000,
            'total' => 10,
            'due_amount' => 45,
            'created_by' => 2
        ];


        return $model->invoices()->create($invoice);

    }

    private function add_items_to_invoice($invoice)
    {
        $invoice->items()->createMany([
            ['name' => 'product 1', 'price' => 100, 'qty' => 2, 'total' => 200],
            ['name' => 'product 2', 'price' => 150, 'qty' => 1, 'total' => 150],
        ]);

        return $invoice;
    }

    public function test_saveInvoiceToDatabase()
    {
        $this->make_invoice();

        $this->assertCount(1, Invoice::all());
        $this->assertEquals(10, Invoice::first()->total);

    }

    public function test_saveSingleItemToInvoice()
    {
        $invoice = $this->make_invoice();
        $item = [
            'name' => "item 01",
            'description' => 'tiny description',
            'price' => 150,
            'qty' => 1,
            'unit' => 'Nos',
            'tag' => '',
            'discount' => 0,
            'discount_value' => 0,
            'discount_type' => '',
            'total' => 150
        ];

        $invoice->items()->create($item);

        $this->assertCount(1, $invoice->items);
        $this->assertEquals(150, $invoice->items->first()->price);
    }

    public function test_saveMultipleItemsToInvoice()
    {
        $invoice = $this->make_invoice();

        $invoice->items()->createMany([
            ['name' => 'product 1', 'price' => 100, 'qty' => 2, 'total' => 452],
            ['name' => 'product 2', 'price' => 100, 'qty' => 10, 'total' => 452],
        ]);

        // count invoice items
        $this->assertCount(2, $invoice->items);
        // check invoice total
        $this->assertEquals(10, $invoice->total);
        // check due balance
        $this->assertEquals(45, $invoice->due_amount);

    }

    public function test_saveInvoiceExtra()
    {
        $invoice = $this->make_invoice();
        $invoice->setExtraValue('fax', '012542856625');


        $this->assertEquals('012542856625', $invoice->extraValue('fax'));
    }

    public function test_calculateInvoiceTotal()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice());

        $this->assertEquals(350, $invoice->total());
    }

    public function test_setFlatDiscount()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice()); // total 350
        $invoice->setDiscount(50);

        $this->assertEquals(300, $invoice->totalWithDiscount());

    }

    public function test_setPercentageDiscount()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice()); // total 350
        $invoice->setDiscount('10%');

        dd($invoice);
        $this->assertEquals(35, $invoice->discount_value);

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

    public function test_payForInvoice()
    {
        $inv = $this->make_invoice();

        $inv->addPayment(100);
        $inv->addPayment(450);
        $inv->addPayment(-50);

        $this->assertDatabaseCount('laravel_invoice_payments', 3);

        $this->assertEquals(500, $inv->payments()->sum('amount'));

    }


}
