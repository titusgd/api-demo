<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Invoice\InvoiceCheckService;

class TravelInvoiceCheck extends Command
{
    protected $signature = 'TravelInvoiceCheck';
    protected $description = '檢查電子收據';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        (new InvoiceCheckService())->check();

        (new InvoiceCheckService())->touchInvoiceIssue();

    }

}
