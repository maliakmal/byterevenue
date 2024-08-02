<?php

namespace App\Http\Controllers;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Services\Campaign\CampaignService;
use App\Jobs\ProcessCsvQueueBatch;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use App\Models\BroadcastLog;
use App\Models\CampaignShortUrl;
use App\Models\BatchFile;
use App\Models\UrlShortener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobsController extends Controller
{
    public function __construct(
        protected CampaignShortUrlRepositoryInterface $campaignShortUrlRepository,
        protected CampaignRepositoryInterface $campaignRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
    )
    {
    }

    public function index(Request $request){

        $download_me = null;
        $urlShorteners = UrlShortener::select()->onlyRegistered()->get();
        if ($request->isMethod('post')) {
            $unique_campaigns = collect();
            $unique_campaign_map = [];

            $total = $request->number_messages;
            $url_shortener = $request->url_shortener;
            $domain_id = UrlShortener::where('name', $url_shortener)->first()->asset_id;

            $batchSize = 100;

            $numBatches = ceil($total / $batchSize);
            $campaign_short_url_map = []; // maps campaign_id -> short url
            $campaign_service = new CampaignService();
            $batch_no = preg_replace("/[^A-Za-z0-9]/", '', microtime());

            $filename = '/csv/byterevenue-messages-' . $batch_no . '.csv';

            $batch_file = BatchFile::create(['filename' => $filename,
                    'path' => env('DO_SPACES_ENDPOINT') . $filename,
                    'number_of_entries' => $total,
                    'is_ready'=>0,
                    'campaign_id' => 0]);
            
            for ($batch = 0; $batch < $numBatches; $batch++) {
                $offset = $batch * $batchSize;
                $is_last = $batch ==($numBatches+1)?true:false;
                dispatch(new ProcessCsvQueueBatch($offset, $batchSize, $url_shortener, $batch_no, $batch_file, $is_last));

            }
            return redirect()->route('jobs.index')->with('success', 'CSV is being generated.');



        }


        $directory = storage_path('app/csv/');
        $files = BatchFile::select()->orderby('id', 'desc')->paginate(15);

        // get count of all messages in the queue
        $params['total_in_queue'] = BroadcastLog::select()->count();
        $params['files'] = $files;
        $params['download_me'] = $download_me;
        $params['urlShorteners'] = $urlShorteners;
        $params['total_not_downloaded_in_queue'] = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();
        return view('jobs.index', compact('params'));
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
