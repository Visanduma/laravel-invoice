<?php

if (!function_exists('money')) {

    function money($amount)
    {

        $currencySymbol = \Visanduma\LaravelInvoice\Helpers\MoneyFormatter::getCurrency();

        return $currencySymbol . number_format($amount, 2);
    }

}
