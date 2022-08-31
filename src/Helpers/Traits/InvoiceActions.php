<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Illuminate\Support\Str;

trait InvoiceActions
{

    public function setDiscount($amount)
    {
        $isPercentage = Str::endsWith($amount, "%");
        $amount = (double)$amount;

        $this->update([
            'discount_type' => $isPercentage ? 'percentage' : 'amount',
            'discount_value' => $amount,
            'discount' => $isPercentage ? ($this->total * $amount / 100) : $amount,
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

    public static function getNextInvoiceNumber($prefix = "INV")
    {
        $next_id = static::max('id') + 1;
        return $prefix . Str::padLeft($next_id, 6, "0");
    }

}
