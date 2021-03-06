<?php

namespace Visanduma\LaravelInvoice\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Visanduma\LaravelInvoice\LaravelInvoiceServiceProvider;
use Visanduma\LaravelInvoice\Tests\database\TestTable;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupMigrations();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'Visanduma\\LaravelInvoice\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelInvoiceServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

    }

    protected function setupMigrations()
    {
        include_once __DIR__ . '/database/TestTable.php';
        (new TestTable())->up();

        include_once __DIR__ . '/../database/migrations/create_invoices_table.php';
        (new \CreateInvoiceTables())->up();

    }
}
