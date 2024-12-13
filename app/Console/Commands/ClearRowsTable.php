<?php

namespace App\Console\Commands;

use App\Models\Row;
use Illuminate\Console\Command;

class ClearRowsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-rows-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Row::truncate();
    }
}
