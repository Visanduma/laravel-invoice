<?php

namespace Visanduma\LaravelInvoice;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelInvoiceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-invoice')
            ->hasConfigFile()
            ->hasMigrations(['create_invoice_tables']);
//            ->hasCommand(LaravelInvoiceCommand::class);
    }
}
