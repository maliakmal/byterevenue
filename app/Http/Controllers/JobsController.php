<?php

namespace App\Http\Controllers;
use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Services\Campaign\CampaignService;
use App\Jobs\ProcessCsvQueueBatch;
use App\Jobs\ProcessCsvRegenQueueBatch;
use App\Jobs\CreateCampaignsOnKeitaro;


use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\CampaignShortUrl;
use App\Models\BatchFile;
use App\Models\UrlShortener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class JobsController extends Controller
{
    public function __construct(
        protected CampaignShortUrlRepositoryInterface $campaignShortUrlRepository,
        protected CampaignRepositoryInterface $campaignRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
    )
    {
    }


    public function campaigns(Request $request){
        // get all campaigns which have messages ready to be sent 
        $uniq_campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDs();
        $user_id = $request->filter_client?$request->filter_client:null;
        if($user_id > 0){
            $campaigns = $this->campaignRepository->getUnsentByIdsOfUser($uniq_campaign_ids->toArray(), $user_id);
        }else{
            $campaigns = $this->campaignRepository->getUnsentByIds($uniq_campaign_ids->toArray());
        }

        $urlShorteners = UrlShortener::select()->onlyRegistered()->orderby('id', 'desc')->get();

        $params = [];
        $params['clients'] = \App\Models\User::all();
        $params['selected_client'] = $user_id;
        $queue_stats = $this->broadcastLogRepository->getQueueStats();
        $params['total_in_queue'] = $queue_stats['total_in_queue'];//BroadcastLog::select()->count();
        $params['campaigns'] = $campaigns;
        $params['files'] = [];
        $params['urlShorteners'] = $urlShorteners;
        $params['total_not_downloaded_in_queue'] = $queue_stats['total_not_downloaded_in_queue'];//BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();

        return view('jobs.campaigns', compact('params'));

    }

    public function index(Request $request){

        $download_me = null;
        $urlShorteners = UrlShortener::select()->onlyRegistered()->orderby('id', 'desc')->get();
        if ($request->isMethod('post')) {
            $unique_campaigns = collect();
            $unique_campaign_map = [];


            $total = $request->number_messages;
            $url_shortener = $request->url_shortener;
            $_url_shortener = UrlShortener::where('name', $url_shortener)->first();
            $domain_id = $_url_shortener->asset_id;
            $campaign_service = new CampaignService();
            $campaign_short_urls = [];
            $batchSize = 100;
            $type = 'fifo';
            $type_id = null;

            if($request->type == 'campaign'){

                $uniq_campaign_ids = $request->campaign_ids;

                $_uniq_campaign_ids = array_filter($uniq_campaign_ids, function($value) {
                    return $value !== 0 && !empty($value);
                });
                $uniq_campaign_ids = $_uniq_campaign_ids;
                foreach($uniq_campaign_ids as $uniq_campaign_id):

                    if(!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $url_shortener)){
                        $alias_for_campaign = uniqid();
                        $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);

                        $_campaign_short_url = $this->campaignShortUrlRepository->create([
                            'campaign_id' => $uniq_campaign_id,
                            'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                            'campaign_alias' => $alias_for_campaign,
                            'url_shortener_id'=>$_url_shortener->id,
                            'deleted_on_keitaro'=>false
                        ]);
                        $campaign_short_urls[] = $_campaign_short_url;
                    }
                endforeach;
                $type = 'campaign';
                $type_id = $uniq_campaign_ids;
                //$uniq_campaign_ids = [$uniq_campaign_id];

            }else{
                $uniq_campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDs($total);

                $_uniq_campaign_ids = array_filter($uniq_campaign_ids->toArray(), function($value) {
                    return $value !== 0 && !empty($value);
                });
                $uniq_campaign_ids = $_uniq_campaign_ids;
                foreach($uniq_campaign_ids as $uniq_campaign_id):
                    if(!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $url_shortener)){
                        $alias_for_campaign = uniqid();
                        $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);
    
                        $_campaign_short_url = $this->campaignShortUrlRepository->create([
                            'campaign_id' => $uniq_campaign_id,
                            'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                            'campaign_alias' => $alias_for_campaign,
                            'url_shortener_id'=>$_url_shortener->id,
                            'deleted_on_keitaro'=>false
                        ]);
                        $campaign_short_urls[] = $_campaign_short_url;
        
                    }
    
                endforeach;
    
            }


            $numBatches = ceil($total / $batchSize);
            $campaign_short_url_map = []; // maps campaign_id -> short url
            $batch_no = preg_replace("/[^A-Za-z0-9]/", '', microtime());

            $filename = '/csv/byterevenue-messages-' . $batch_no . '.csv';

            $batch_file = BatchFile::create(['filename' => $filename,
                    'path' => env('DO_SPACES_ENDPOINT') . $filename,
                    'number_of_entries' => $total,
                    'is_ready'=>0]);

            $batch_file->campaigns()->attach($uniq_campaign_ids);
            
            Log::info('numBatches - '.$numBatches);
            for ($batch = 0; $batch < $numBatches; $batch++) {
                $offset = $batch * $batchSize;
                $is_last = $batch ==($numBatches-1)?true:false;
                Log::info('BATCH number - '.$batch);
                Log::info('BATCH number - '.$numBatches);
                $params = array();
                $params['offset'] = $offset;
                $params['batchSize'] = $batchSize;
                $params['url_shortener'] = $url_shortener;
                $params['batch_no'] = $batch_no;
                $params['batch_file'] = $batch_file;
                $params['is_last'] = $is_last;
                $params['type'] = $type;
                $params['type_id'] = $type_id;
                dispatch(new ProcessCsvQueueBatch($params));//$offset, $batchSize, $url_shortener, $batch_no, $batch_file, $is_last, $type, $type_id));

            }
            
            $params = ['campaigns'=>$campaign_short_urls, 'domain_id'=>$domain_id];
            dispatch(new CreateCampaignsOnKeitaro($params));
            if($request->ajax()){


                $one = $batch_file->toArray();
                $_batch_no = $batch_file->getBatchFromFilename();
                // get all entries with the campaig id and the batch no
                $specs = $this->broadcastLogRepository->getTotalSentAndClicksByBatch($_batch_no);
                $one['total_entries'] = $specs['total'];
                $one['total_sent'] =  $specs['total_sent'];
                $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
                $one['total_clicked'] = $specs['total_clicked'];
                
                $one['created_at_ago'] = $batch_file->created_at->diffForHumans();;
    
    


                return response()->json(['data'=>$one]);
            }else{
                return redirect()->route('jobs.index')->with('success', 'CSV is being generated.');
            }
        }


        $directory = storage_path('app/csv/');
        $files = BatchFile::select()->orderby('id', 'desc')->paginate(15);

        $batches = [];
        // get individual batches
        foreach($files as $_file){
            $batches[] = $_file->getBatchFromFilename();
        }

        $message_ids = BroadcastLog::whereIn('batch', $batches)->distinct()->pluck('message_id');


        // get count of all messages in the queue
        $queue_stats = $this->broadcastLogRepository->getQueueStats();
        $params['total_in_queue'] = $queue_stats['total_in_queue'];//BroadcastLog::select()->count();
        $params['files'] = $files;
        $params['download_me'] = $download_me;
        $params['urlShorteners'] = $urlShorteners;
        $params['total_not_downloaded_in_queue'] = $queue_stats['total_not_downloaded_in_queue'];// BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();
        return view('jobs.index', compact('params'));
    }

    public function regenerateUnsent(Request $request){
        // get all unsent
        $batch_id = $request->batch;
        $_batch = BatchFile::find($batch_id);
        preg_match('/byterevenue-[^\/]*-(.*?)\.csv/', $_batch->filename, $matches);
        if(!$matches[1]){
            return redirect()->route('jobs.index')->with('error', 'Something went wrong - csv could not be generated.');
        }else{
            $batch = $matches[1];
        }
        $url_shortener = $request->url_shortener;
        $_url_shortener = UrlShortener::where('name', $url_shortener)->first();
        $domain_id = $_url_shortener->asset_id;
        $original_batch_no = $batch;
        $campaign_short_urls = [];
        $batchSize = 100;
        $unsent_logs = $this->broadcastLogRepository->getUnsent(['batch'=>$batch]);
        $total = count($unsent_logs);
        $uniq_campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDsFromExistingBatch($batch);
        $type = 'fifo';
        $type_id = null;
        $message_id = null;
        $campaign_service = new CampaignService();

        if($request->type == 'campaign'){
            $campaign_ids = $request->campaign_ids;
            if(count($campaign_ids) == 1){
                $campaign = Campaign::find($campaign_ids[0]);
                if($campaign->message->body != $request->message_body){
                    $new_message = $campaign->message->replicate();
                    $new_message->body = $request->message_body;
                    $new_message->save();
                    $message_id = $new_message->id;
                }
            }

            $uniq_campaign_ids = $campaign_ids;

            foreach($uniq_campaign_ids as $uniq_campaign_id):

                if(!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $url_shortener)){
                    $alias_for_campaign = uniqid();
                    $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);

                    $_campaign_short_url = $this->campaignShortUrlRepository->create([
                        'campaign_id' => $uniq_campaign_id,
                        'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                        'campaign_alias' => $alias_for_campaign,
                        'url_shortener_id'=>$_url_shortener->id,
                        'deleted_on_keitaro'=>false
                    ]);
                    $campaign_short_urls[] = $_campaign_short_url;
                }
            endforeach;

            $type = 'campaign';
            $type_id = $uniq_campaign_ids;

        }else{

            foreach($uniq_campaign_ids as $uniq_campaign_id):
                // if there is no existing keitaro camp id for this campaign + url combo - create one
                if(!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $url_shortener)){
                    $alias_for_campaign = uniqid();
                    $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);

                    $_campaign_short_url = $this->campaignShortUrlRepository->create([
                        'campaign_id' => $uniq_campaign_id,
                        'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                        'campaign_alias' => $alias_for_campaign,
                        'url_shortener_id'=>$_url_shortener->id,
                        'deleted_on_keitaro'=>false
                    ]);
                    $campaign_short_urls[] = $_campaign_short_url;
                }

            endforeach;
    }

        $numBatches = ceil($total / $batchSize);
        $campaign_short_url_map = []; // maps campaign_id -> short url
        $batch_no = $batch.'_1';
        $filename = '/csv/byterevenue-regen-' . $batch_no . '.csv';

        $batch_file = BatchFile::create(['filename' => $filename,
                'path' => env('DO_SPACES_ENDPOINT') . $filename,
                'number_of_entries' => $total,
                'is_ready'=>0]);
        $batch_file->campaigns()->attach($uniq_campaign_ids);

        Log::info('numBatches - '.$numBatches);
        for ($batch = 0; $batch < $numBatches; $batch++) {
            $offset = $batch * $batchSize;
            $is_last = $batch ==($numBatches+1)?true:false;
            Log::info('BATCH number - '.$batch);
            dispatch(new ProcessCsvRegenQueueBatch($offset, $batchSize, $url_shortener, $original_batch_no, $batch_no, $batch_file, $is_last, $type, $type_id, $message_id));
        }
        
        $params = ['campaigns'=>$campaign_short_urls, 'domain_id'=>$domain_id];
        dispatch(new CreateCampaignsOnKeitaro($params));
        $original_filename = '/csv/byterevenue-messages-' . $batch. '.csv';

        $original_batch_file = BatchFile::select()->where('filename', $original_filename)->get()->first();
        if($original_batch_file){
            $original_batch_file->number_of_entries-=$total;
            $original_batch_file->save();
        }

        if($request->ajax()){
            return response()->json(['data'=>$batch_file]);
        }else{
            return redirect()->route('jobs.index')->with('success', 'CSV is being generated.');
        }

    }

    public function downloadFile($filename)
    {
        $batch = BatchFile::find($filename);
        $response = new StreamedResponse(function () use($batch) {
            $handle = fopen('php://output', 'w');
            // Output the column headings
            fputcsv($handle, ['UID','Phone', 'Subject', 'Text']);

            $batch_no = $batch->getBatchFromFilename();
            
            // Query and write data to the file
            $rows = BroadcastLog::select()->where('batch', '=', $batch_no)->orderby('id', 'ASC')->cursor();
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->recipient_phone,
                    '',
                    $row->message_body,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'.csv"');

        return $response;

    }

    public function download(Request $request){

        // if user posted to select n non downloaded messages
        $limit = $request->limit;
        $Limit = $limit > 0 ? $limit : 100;
        $shortener = $request->shortener;
        $messages = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->orderby('id', asc)->take($limit)->get();

        BroadcastLog::where('id', '<=', BroadcastLog::where('is_downloaded_as_csv', 0)->orderby('id', asc)->take($limit)->get()->last()->id)
        ->update(['is_downloaded_as_csv' => 1]);


    }

    public function updateSentMessage(Request $request)
    {
        $request->validate(['uid' => 'required|numeric|min:1']);
        $uid = $request->uid;
        $model = $this->broadcastLogRepository->find($uid);
        if(!$model){
            return response()->error('not found');
        }
        if(!$this->broadcastLogRepository->updateByModel([
            'sent_at' => Carbon::now(),
            'is_sent' => true,
            'status' => BroadcastLogStatus::SENT,
        ], $model)){
            return response()->error('update failed');
        }
        return response()->success();
    }

    public function updateClickMessage(Request $request)
    {
        $request->validate(['uid' => 'required|numeric|min:1']);
        $uid = $request->uid;
        $model = $this->broadcastLogRepository->find($uid);
        if(!$model){
            return response()->error('not found');
        }
        if(!$this->broadcastLogRepository->updateByModel([
            'is_click' => true,
            'clicked_at' => Carbon::now(),
        ], $model)){
            return response()->error('update failed');
        }
        return response()->success();
    }

}
