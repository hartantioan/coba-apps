<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DeleteOldWeightHistories;

class DeleteOldWeightHistoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weight-histories:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete weight histories older than 7 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DeleteOldWeightHistories::dispatch();
        $this->info('Old weight histories deleted successfully.');
        return 0;
    }
}
