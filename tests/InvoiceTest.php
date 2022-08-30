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

    private function make_invoice($number = 'XA12345'): Invoice
    {
        $invoice = [
            'invoice_date' => now(),
            'tag' => 'prime',
            'due_data' => now()->addDay(),
            'invoice_number' => $number,
            'status' => Invoice::STATUS_DRAFT,
            'paid_status' => Invoice::STATUS_UNPAID,
            'note' => '',
            'discount' => 0,
            'discount_value' => 0,
            'discount_type' => 1,
            'sub_total' => 0,
            'total' => 0,
            'due_amount' => 0,
            'created_by' => 2
        ];


        return Invoice::create($invoice);
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
        $invoice = $this->make_invoice();
        $this->invoiceAble->invoices()->save($invoice);

        $this->assertCount(1, $this->invoiceAble->invoices);
    }

    public function test_saveSingleItemToInvoice()
    {
        $invoice = $this->make_invoice();

        $item = [
            'name' => "item 01",
            'description' => 'tiny description',
            'price' => 150,
            'qty' => 15,
            'unit' => 'Nos',
            'tag' => '',
            'discount' => 0,
            'discount_type' => InvoiceItem::DISCOUNT_FLAT,
        ];

        $invoice->items()->create($item);

        $this->assertCount(1, $invoice->items);
    }

    public function test_saveMultipleItemsToInvoice()
    {
        $invoice = $this->make_invoice();

        $invoice->items()->createMany([
            ['name' => 'product 1', 'price' => 100, 'qty' => 2],
            ['name' => 'product 2', 'price' => 100, 'qty' => 10],
        ]);

        // count invoice items
        $this->assertCount(2, $invoice->items);
    }

    public function test_saveInvoiceExtra()
    {
        $invoice = $this->make_invoice();
        $invoice->setExtraValue('fax', '012542856625');


        $this->assertEquals('012542856625', $invoice->getExtraValue('fax'));
    }

    public function test_calculateInvoiceTotal()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice());

        $this->assertEquals(350, $invoice->total());
    }

    public function test_setFlatDiscount()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice()); // total 350
        $invoice->setDiscount(150);

        $this->assertEquals(200, $invoice->totalWithDiscount());
    }

    public function test_setPercentageDiscount()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice()); // total 350
        $invoice->setDiscount('50%');

        $this->assertEquals(175, $invoice->totalWithDiscount());
    }

    public function test_ableToFindInvoices()
    {

        $this->invoiceAble->attachInvoice($this->make_invoice("f123"));

        // find by invoice number
        $this->assertNotNull($this->invoiceAble->findInvoiceByNumber("f123"));
    }

    public function test_payForInvoice()
    {
        $inv = $this->make_invoice();

        $inv->addPayment(100);
        $inv->addPayment(450);
        $inv->addPayment(-50);

        $this->assertDatabaseCount('laravel_invoice_payments', 3);
        $this->assertEquals(500, $inv->paidAmount());
    }

    public function test_updateInvoiceStatus()
    {
        $inv = $this->make_invoice();

        $this->assertEquals(Invoice::STATUS_DRAFT, $inv->status);

        $inv->setStatus(Invoice::STATUS_COMPLETED);

        $this->assertEquals(Invoice::STATUS_COMPLETED, $inv->status);

        $this->assertEquals('COMPLETED', $inv->statusToString());


    }

    public function test_updateInvoicePaymentStatus()
    {
        $inv = $this->make_invoice();

        $this->assertEquals(Invoice::STATUS_UNPAID, $inv->paid_status);

        $inv->setPaymentStatus(Invoice::STATUS_PAID);

        $this->assertEquals(Invoice::STATUS_PAID, $inv->paid_status);


    }

}
