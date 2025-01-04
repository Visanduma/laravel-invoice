<?php

namespace Visanduma\LaravelInvoice;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use Visanduma\LaravelInvoice\Services\InvoiceObserverService;
use Visanduma\LaravelInvoice\Observers\InvoiceObserver;

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
            ->hasMigrations(['create_invoice_tables', 'add_invoice_type', 'make_common_extra']);
        //            ->hasCommand(LaravelInvoiceCommand::class);
    }
    public function boot()
    {
        InvoiceObserverService::register(InvoiceObserver::class);
    }
}
