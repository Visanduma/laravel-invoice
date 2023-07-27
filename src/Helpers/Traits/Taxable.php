<?php

namespace Visanduma\LaravelInvoice\Helpers\Traits;

trait Taxable
{

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
        return $this->extra()->where('key', 'taxes')
            ->get()
            ->map(function ($el) {
                return json_decode($el->value, true);
            })->toArray();
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

    public function totalTaxAmount()
    {
        return array_sum(array_column($this->getTaxesWithAmount(), 'amount'));
    }
}
