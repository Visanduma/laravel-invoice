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


    private function make_invoice(): Invoice
    {
        $invoice = [
            'invoice_date' => now(),
            'tag' => 'prime',
//            'note' => '',
//            'discount' => 0,
//            'discount_value' => 0,
//            'discount_type' => 1,
//            'sub_total' => 0,
//            'total' => 0,
//            'due_amount' => 0,
//            'created_by' => 2
//            'paid_status' => Invoice::STATUS_UNPAID,
//            'status' => Invoice::STATUS_DRAFT,
//            'invoice_number' => $number,
//            'due_date' => now()->addDay(),
        ];


        return Invoice::create($invoice);
    }

    private function add_items_to_invoice($invoice)
    {
        $invoice->items()->createMany([
            ['name' => 'product 1', 'price' => 100, 'qty' => 2 ],
            ['name' => 'product 2', 'price' => 150, 'qty' => 1],
        ]);

        $invoice->refresh();

        return $invoice;
    }

    public function test_createInvoiceForModel()
    {
        $this->invoiceAble->invoices()->create([
            'invoice_date' => now()
        ]);

        $this->assertDatabaseCount('laravel_invoices', 1);

        $inv = Invoice::make();

        $this->invoiceAble->attachInvoice($inv);

        $this->assertDatabaseCount('laravel_invoices', 2);
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

    public function test_applyPerItemDiscount()
    {
        $inv = $this->make_invoice();

        $this->add_items_to_invoice($inv);

        $this->assertEquals(350, $inv->getItemsTotal());

        $inv->items->first()->setDiscount(20);

        $this->assertEquals(330, $inv->getItemsTotal());
    }

    public function test_saveInvoiceExtra()
    {
        $invoice = $this->make_invoice();
        $invoice->setExtraValue('fax', '012542856625');

        $this->assertEquals('012542856625', $invoice->getExtraValue('fax'));

        $invoice->setExtraValue('tax', 'tax 1');
        $invoice->setExtraValue('tax', 'tax 2');

        $invoice->setExtraValues([
            'me' => 'yes',
            'she' => 'no'
        ]);

        $this->assertIsArray($invoice->getExtraValue('tax'));
        $this->assertEquals('yes', $invoice->getExtraValue('me'));
    }

    public function test_calculateInvoiceTotal()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice());

        $this->assertEquals(350, $invoice->getItemsTotal());
    }

    public function test_setFlatInvoiceDiscount()
    {
        $invoice = $this->add_items_to_invoice($this->make_invoice()); // total 350
        $invoice->setDiscount(150);

        $this->assertEquals(200, $invoice->total);
        $this->assertEquals(150, $invoice->discount_value);
        $this->assertEquals(150, $invoice->discount);
        $this->assertEquals('amount', $invoice->discount_type);
    }

    public function test_setPercentInvoiceDiscount()
    {
        $invoice = $this->make_invoice(); // total 350
        $this->add_items_to_invoice($invoice);

        $invoice->setDiscount('20%');

        $this->assertEquals(280, $invoice->total);
        $this->assertEquals(20, $invoice->discount_value);
        $this->assertEquals(70, $invoice->discount);
    }

    public function test_ableToFindInvoices()
    {
        $this->invoiceAble->attachInvoice($this->make_invoice());

        // find by invoice number
        $this->assertNotNull($this->invoiceAble->findInvoiceByNumber("INV000001"));
    }

    public function test_payForInvoice()
    {
        $inv = $this->make_invoice();
        $this->add_items_to_invoice($inv); // total 350

        $inv->addPayment(100);
        $inv->addPayment(100);
        $inv->addPayment(-50);

        $this->assertDatabaseCount('laravel_invoice_payments', 3);
        $this->assertEquals(150, $inv->paidAmount());
        $this->assertEquals(200, $inv->dueAmount()); // check due balance
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

    public function test_InvoiceItemHelpers()
    {
        $inv = $this->make_invoice();

        $item = InvoiceItem::create([
            'name' => 'product 1',
            'price' => 100,
            'qty' => 2,
            'invoice_id' => $inv->id,
        ]);

        $item->setExtraValue('batch','bb678');

        $this->assertEquals('bb678',$item->getExtraValue('batch'));

        $this->assertEquals(200, $item->total);

        $item->setDiscount('10%');
        $this->assertEquals(180, $item->total);

        $item->setDiscount(50);
        $this->assertEquals(150, $item->total);

        $this->assertEquals(200, $item->totalWithoutDiscount());
    }

    public function test_invoiceTaxes()
    {
        $inv = $this->make_invoice();

        $this->add_items_to_invoice($inv); // total 350

        $inv->addTax('VAT', 8);

        $this->assertEquals(28, $inv->totalTaxAmount());

        $inv->addTax('FAT', 2);

        $this->assertEquals(35, $inv->totalTaxAmount());

        $this->assertEquals(385, $inv->total);
    }

    public function test_invoiceHelpers()
    {
        $inv = $this->make_invoice();
        $this->add_items_to_invoice($inv); // total 350

        $this->assertEquals(350, $inv->getItemsTotal());
        $this->assertEquals(2, $inv->getItemCount());
        $this->assertEquals(0, $inv->paidAmount());

        $this->assertNotEmpty(Invoice::getNextInvoiceNumber());
        $this->assertEquals('DRAFT', $inv->statusToString());

        $inv->addPayment(50);
        $inv->addPayment(150);

        $this->assertEquals(200, $inv->paidAmount());
    }

    public function test_generateInvoiceDataArray()
    {
        $inv = $this->make_invoice();
        $this->add_items_to_invoice($inv);

        $inv->setCurrency('Rs.');

        $inv->setExtraValues([
            'to.name' => 'Customer name',
            'to.address' => 'Street one, City, PO CODE',
            'from.name' => 'Seller name',
            'from.address' => 'Seller Street one, City, PO CODE',
        ]);

        $inv->addPayment(50, 'I paid');

        $inv->addTax('VAT', 12);
        $inv->addTax('NBT', 5);

        $this->assertIsArray($inv->toArray());
    }

}
