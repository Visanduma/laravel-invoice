<?php

namespace Visanduma\LaravelInvoice\Helpers\Traits;

use Illuminate\Support\Str;
use Visanduma\LaravelInvoice\Helpers\MoneyFormatter;

trait InvoiceActions
{

    public function addPayment($amount,  $note = null, $method = 'CASH')
    {
        if ($amount == 0) {
            return;
        }
        $this->payments()->create([
            'method' => $method,
            'amount' => $amount,
            'payment_date' => now(),
            'note' => $note
        ]);

        $this->decrement('due_amount', $amount);
        $this->paid_status = self::STATUS_PAID;
        $this->save();
    }

    public function setDiscount($amount)
    {
        $isPercentage = Str::endsWith($amount, "%");
        $amount = (float)$amount;

        $this->update([
            'discount_type' => $isPercentage ? 'percentage' : 'amount',
            'discount_value' => $amount,
            'discount' => $isPercentage ? ($this->getItemsTotal() * $amount / 100) : $amount,
        ]);

        $this->updateCalculation();
    }

    public function addItems(array $items)
    {
        $this->items()->saveMany($items);
    }

    public function getItemCount()
    {
        return $this->items->count();
    }

    public function getItemsTotal()
    {
        return $this->items()->sum('total');
    }

    public function paidAmount()
    {
        return $this->payments()->sum('amount');
    }

    public static function getNextInvoiceNumber()
    {
        $next_id = self::max('id') + 1;
        return config('invoice.prefix') . Str::padLeft($next_id, 6, "0");
    }

    public function setCurrency($currency = '$')
    {
        $this->setExtraValue('currency', $currency);
    }

    public function getCurrency(): string
    {
        return $this->getExtraValue('currency');
    }

    public function dueAmount()
    {
        return $this->due_amount;
    }

    public function asArray()
    {

        $this->load('items', 'payments', 'extra');

        MoneyFormatter::setCurrency($this->getCurrency());

        return [
            'from' => [
                'name' => $this->getExtraValue('seller.name'),
                'address' => $this->getExtraValue('seller.address'),
                'phone' => $this->getExtraValue('seller.phone'),
                'email' => $this->getExtraValue('seller.email'),
                'extra' => ''
            ],
            'to' => [
                'name' => $this->getExtraValue('customer.name'),
                'address' => $this->getExtraValue('customer.address'),
                'phone' => $this->getExtraValue('customer.phone'),
                'email' => $this->getExtraValue('customer.email'),
                'extra' => ''
            ],
            'items' => $this->items->toArray(),
            'payments' => $this->payments->toArray(),
            'taxes' => $this->getTaxesWithAmount(),
            'summary' => [
                'items_count' => $this->getItemCount(),
                'gross_total' => ($this->getItemsTotal()), // without invoice tax/discount
                'total' => ($this->total),
                'discount' => ($this->discount),
                'paid_amount' => ($this->paidAmount()),
                'due_amount' => ($this->due_amount),
                'tax_total' => ($this->totalTaxAmount()),
                'status' => $this->statusToString(),
                'date' => $this->created_at->toDateTimeString(),
                'currency' => $this->getCurrency(),
            ]
        ];
    }
}
