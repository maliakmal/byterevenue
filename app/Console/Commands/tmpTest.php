<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $_dateFormat      = 'm/d/Y';
        $start_date       = Carbon::now()->subDay()->format($_dateFormat);
        $end_date         = Carbon::now()->addDay()->format($_dateFormat);

        $startDate      = Carbon::createFromFormat($_dateFormat, $start_date);
        $endDate        = Carbon::createFromFormat($_dateFormat, $end_date);

        $result = \DB::connection('mysql')
        ->table('broadcast_logs')
        ->select(DB::raw("DATE(clicked_at) AS date, COUNT(*) AS count"))
        ->whereNotNull('clicked_at')
        ->where('clicked_at', '>=', $startDate->toDateTimeString())
        ->where('clicked_at', '<=', $endDate->toDateTimeString())
        ->groupBy(DB::raw('DATE(clicked_at)'))
        ->get();

        dd($result);
    }
}
