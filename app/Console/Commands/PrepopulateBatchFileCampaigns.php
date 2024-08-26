<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BatchFile;
use App\Models\BroadcastLog;
use Illuminate\Support\Facades\Log;

class PrepopulateBatchFileCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prepopulate-batch-file-campaigns';

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
        //
        $batchFilesWithoutCampaigns = BatchFile::doesntHave('campaigns')->get();

        foreach($batchFilesWithoutCampaigns as $batch_file){
            $batch = $batch_file->getBatchFromFilename();
            $campaign_ids = BroadcastLog::where('batch', $batch)->distinct()->pluck('campaign_id')->toArray();
            $cleaned_campaign_ids = array_filter($campaign_ids, function($value) {
                return $value !== 0 && $value !== null;
            });
            
            $batch_file->campaigns()->attach($cleaned_campaign_ids);
            $this->info('Attached campaign ids '.(join(',', $campaign_ids)).' to batchfile '.$batch_file->filename);

        }

        $this->info('All Done :)');
    }
}
