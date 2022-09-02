# Laravel Invoice

[![Latest Version on Packagist](https://img.shields.io/packagist/v/visanduma/laravel-invoice.svg?style=flat-square)](https://packagist.org/packages/visanduma/laravel-invoice)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/visanduma/laravel-invoice/run-tests?label=tests)](https://github.com/visanduma/laravel-invoice/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/visanduma/laravel-invoice/Check%20&%20fix%20styling?label=code%20style)](https://github.com/visanduma/laravel-invoice/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/visanduma/laravel-invoice.svg?style=flat-square)](https://packagist.org/packages/visanduma/laravel-invoice)

Yet another simple & powerful invoicing package for Laravel

## Installation

You can install the package via composer:

```bash
composer require visanduma/laravel-invoice
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-invoice-migrations"
php artisan migrate
```

## Usage

### Model configuration

Make any model invoice-able using ```InvoiceAble``` trait

```php

use Visanduma\LaravelInvoice\Helpers\Traits\InvoiceAble;

class Customer extends Model
{
    use InvoiceAble;

	...
}
```

now your model is ready to create invoices

### Create invoice

```php

$customer->invoices()->create([
	'invoice_date' => now(),
	'tag' => 'default'  // optional. just for identification
])

// or

$invoice = Invoice::make();
$customer->attachInvoice($invoice)
```

### Add items to invoice

```php

// adding single item
$invoice->items()->create([
	'name' => 'Product one',
	'price' => 150,
	'qty' => 1,
	// optional fields
	'description' => 'tiny description',
	'unit' => 'Nos',
    'tag' => '',
    'discount' => 0,
    'discount_type' => InvoiceItem::DISCOUNT_FLAT,
]);

// adding multiple items

$invoice->items()->createMany([
	['name' => 'Product 2', 'price' => 100, 'qty' => 2],
    ['name' => 'Product 3', 'price' => 80, 'qty' => 1],
]);

```

### Adding additional information to invoice

```php
// adding single value
$invoice->setExtraValue('something','and its value') 

// adding multiple values at once
$invoice->setExtraValues([
	'something' => '',
	'another thing' => 'another value'
])

// retrieve back extra values
$invoice->getExtraValue('something') // returns 'and its value'
```

### Add payment for invoice

```php
$invoice->addPayment(100)
$invoice->addPayment(100,'advance payment') // pay with note

$invoice->addPayment(-10) // use minus values to make deduction

// get total of paid
$invoice->paidAmount() // 190 (100 + 100 - 10)

// get payment due amount
$invoice->dueAmount()

```

### Add discount/tax to invoice

```php
// set flat discount amount
$invoice->setDiscount(50)

// set percentage of discount
$invoice->setDiscount('5%')

// add tax
$invoice->addTax('VAT', 12) // 12 equals to 12%


```

### Add discount to invoice item

```php
// set flat discount amount
$invoiceItem->setDiscount(50)

// set percentage of discount
$invoiceItem->setDiscount('5%')
```

### Calculation of invoice

```php

$invoice->items()->createMany([
	['name' => 'Product 2', 'price' => 100, 'qty' => 2],
    ['name' => 'Product 3', 'price' => 80, 'qty' => 1],
]);

// get total amount of invoice items

$invoice->getItemsTotal() // 280

```

### Invoice item helpers

```php

$item = InvoiceItem::create([
	'name' => 'Product one',
	'price' => 100,
	'qty' => 1,
])


// Set discount per item
$item->setDiscount(10) // total is 90
$item->setDiscount('50%') // total is 50

// get total amount
$item->total

//get total without discount
$item->totalWithoutDiscount()


```

### Invoice helpers

```php
// find invoice by invoice number
$customer->findInvoiceByNumber('INV000001')

// update invoice status
$invoice->setStatus(Invoice::STATUS_COMPLETED)
$invoice->setStatus(Invoice::STATUS_DRAFT)
$invoice->setStatus(Invoice::STATUS_SENT)

// get status
$invoice->status

// update payment status
$invoice->setPaymentStatus(Invoice::STATUS_UNPAID)
$invoice->setPaymentStatus(Invoice::STATUS_PAID)

// get paid status
$invoice->paid_status

// get invoice total without taxes & invoice discount
$invoice->getItemsTotal()

// invoice item count
$invoice->getItemCount()

// invoice due
$invoice->dueAmount()

// invoiced tax
$invoice->totalTaxAmount()

// invoice total
$invoice->total

// set invoice currency symbol
$invoice->setCurrency('Rs.') // default is $

```

### Generate invoice data array

```php
$invoice->toArray()

// output

[

"from" => [
    "name" => "Seller name"
    "address" => "Seller Street one, City, PO CODE"
    "contact" => ""
    "extra" => ""
  ]
  "to" => [
    "name" => "Customer name"
    "address" => "Street one, City, PO CODE"
    "contact" => ""
    "extra" => ""
  ]
  "items" => [
    0 => [
      "id" => 1
      "invoice_id" => "1"
      "name" => "product 1"
      "description" => null
      "discount" => 0.0
      "discount_value" => 0.0
      "discount_type" => null
      "price" => 100.0
      "qty" => 2
      "unit" => null
      "total" => 200.0
      "item_id" => null
      "tag" => null
      "created_at" => "2022-09-02 07:43:10"
      "updated_at" => "2022-09-02 07:43:10"
    ]
    1 => [
      "id" => 2
      "invoice_id" => "1"
      "name" => "product 2"
      "description" => null
      "discount" => 0.0
      "discount_value" => 0.0
      "discount_type" => null
      "price" => 150.0
      "qty" => 1
      "unit" => null
      "total" => 150.0
      "item_id" => null
      "tag" => null
      "created_at" => "2022-09-02 07:43:10"
      "updated_at" => "2022-09-02 07:43:10"
    ]
  ]
  "payments" => [
    0 =>  [
      "id" => 1
      "invoice_id" => "1"
      "method" => "CASH"
      "amount" => "50"
      "note" => "I paid"
      "payment_date" => "2022-09-02 07:43:10"
      "created_at" => "2022-09-02 07:43:10"
      "updated_at" => "2022-09-02 07:43:10"
    ]
  ]
  "taxes" =>  [
    0 =>  [
      "name" => "VAT"
      "value" => 12
      "amount" => 42
    ]
    1 => [
      "name" => "NBT"
      "value" => 5
      "amount" => 17.5
    ]
  ]
  "summary" => [
    "items_count" => 2
    "gross_total" => "Rs.350.00"
    "total" => "Rs.409.50"
    "discount" => "Rs.0.00"
    "paid_amount" => "Rs.50.00"
    "due_amount" => "Rs.359.50"
    "tax_total" => "Rs.59.50"
    "status" => "DRAFT"
    "date" => "2022-09-02 07:43:10"
    "currency" => "Rs."
  ]

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Visanduma](https://github.com/Visanduma)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
