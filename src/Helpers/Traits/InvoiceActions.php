<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Illuminate\Support\Arr;
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

    public function setExtraValue($key, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->extra()->create([
            'key' => $key,
            'value' => $value
        ]);
    }

    public function setExtraValues(array $values)
    {
        foreach ($values as $key => $value) {
            $this->setExtraValue($key, $value);
        }
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

    public function getExtraValue($key, $default = "")
    {
        $rows = $this->extra()->where('key', $key)->get();

        return $rows->count() > 1
            ? $rows->pluck('value')->toArray()
            : $rows->first()->value ?? $default;
    }

    public function getCurrency(): string
    {
        return $this->getExtraValue('currency');
    }

    public function addTax($name, $percentage)
    {
        $this->setExtraValue('taxes', [
            'name' => $name,
            'value' => $percentage
        ]);

        $this->updateCalculation();
    }

    public function getTax()
    {
        return array_map(
            function ($json) {
                return json_decode($json, true);
            },
            Arr::wrap($this->getExtraValue('taxes'))
        );
    }

    public function dueAmount()
    {
        return $this->due_amount;
    }

    public function totalTaxAmount()
    {
        $totalTaxPercentage = array_sum(array_column($this->getTax(), 'value'));

        return $this->calculateTax($totalTaxPercentage);
    }

    private function calculateTax($tax)
    {
        return $this->getItemsTotal() * $tax / 100;
    }

    public function getTaxesWithAmount()
    {
        return array_map(function ($tax) {
            $tax['amount'] = $this->calculateTax($tax['value']);
            return $tax;
        }, $this->getTax());
    }

    public function asArray()
    {

        $this->load('items', 'payments', 'extra');

        MoneyFormatter::setCurrency($this->getCurrency());

        return [
            'from' => [
                'name' => $this->getExtraValue('from.name'),
                'address' => $this->getExtraValue('from.address'),
                'contact' => $this->getExtraValue('from.contact'),
                'extra' => ''
            ],
            'to' => [
                'name' => $this->getExtraValue('to.name'),
                'address' => $this->getExtraValue('to.address'),
                'contact' => $this->getExtraValue('to.contact'),
                'extra' => ''
            ],
            'items' => $this->items->toArray(),
            'payments' => $this->payments->toArray(),
            'taxes' => $this->getTaxesWithAmount(),
            'summary' => [
                'items_count' => $this->getItemCount(),
                'gross_total' => money($this->getItemsTotal()), // without invoice tax/discount
                'total' => money($this->total),
                'discount' => money($this->discount),
                'paid_amount' => money($this->paidAmount()),
                'due_amount' => money($this->due_amount),
                'tax_total' => money($this->totalTaxAmount()),
                'status' => $this->statusToString(),
                'date' => $this->created_at->toDateTimeString(),
                'currency' => $this->getCurrency(),
            ]
        ];
    }
}
