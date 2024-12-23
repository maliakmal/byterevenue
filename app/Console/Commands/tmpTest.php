<?php

namespace App\Console\Commands;

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
        // test
        \DB::table('contacts')->whereNotNull('phone')
            ->update([
                'phone' => \DB::raw("CAST(REGEXP_REPLACE(phone, '[^0-9]', '') AS UNSIGNED)")
            ]);
    }
}
