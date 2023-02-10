<?php

if (!function_exists('money')) {

    function money($amount, $symbol = false)
    {

        $currencySymbol = \Visanduma\LaravelInvoice\Helpers\MoneyFormatter::getCurrency();

        if (!$symbol) {
            return number_format($amount, 2);
        }

        return $currencySymbol . number_format($amount, 2);
    }
}
