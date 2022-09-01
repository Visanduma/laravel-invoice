<?php


namespace Visanduma\LaravelInvoice\Helpers;


class MoneyFormatter
{
    protected static string $currency;

    public static function getCurrency()
    {
        return self::$currency;
    }

    public static function setCurrency($currency)
    {
        self::$currency = $currency;
    }

}
