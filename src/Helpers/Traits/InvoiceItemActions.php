<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Illuminate\Support\Str;

trait InvoiceItemActions
{

    public function setDiscount($amount)
    {
        $isPercentage = Str::endsWith($amount, "%");
        $amount = (double)$amount;

        $this->update([
            'discount_type' => $isPercentage ? 'percentage' : 'amount',
            'discount_value' => $amount,
            'discount' => $isPercentage ? ($this->price * $this->qty *  $amount / 100) : $amount,
        ]);

        $this->invoice->updateCalculation();

    }

    public function totalWithoutDiscount()
    {
        return ($this->price * $this->qty);
    }

}
