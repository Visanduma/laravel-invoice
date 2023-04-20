<?php

namespace Visanduma\LaravelInvoice\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Visanduma\LaravelInvoice\LaravelInvoiceServiceProvider;
use Visanduma\LaravelInvoice\Tests\database\TestTable;

class TestCase extends Orchestra
{
    public TestModel $invoiceAble;


    protected function setUp(): void
    {
        parent::setUp();
        $this->setupMigrations();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Visanduma\\LaravelInvoice\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->invoiceAble = new TestModel();
        $this->invoiceAble->save();
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

        include_once __DIR__ . '/../database/migrations/create_invoice_tables.php';
        (new \CreateInvoiceTables())->up();

        include_once __DIR__ . '/../database/migrations/add_invoice_type.php';
        (new \AddInvoiceType())->up();

        include_once __DIR__ . '/../database/migrations/make_common_extra.php';
        (new \MakeCommonExtra())->up();

    }
}
