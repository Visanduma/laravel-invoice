<?php

namespace Visanduma\LaravelInvoice\Commands;

use Illuminate\Console\Command;

class LaravelInvoiceCommand extends Command
{
    public $signature = 'laravel-invoice';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
