<?php

namespace App\Console\Commands;

use App\Helpers\MemoryUsageHelper;
use Illuminate\Console\Command;

class tmpTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary test command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $res = MemoryUsageHelper::measureMemoryUsage(function () {
            //
        });

        dd($res);
    }
}
