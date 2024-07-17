<?php

namespace App\Http\Controllers;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Services\Campaign\CampaignService;
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

            $limit = $request->number_messages;
            $url_shortener = $request->url_shortener;
            $domain_id = UrlShortener::where('name', $url_shortener)->first()->asset_id;

            //$logs = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get();
            $logs = BroadcastLog::select()->whereNull('batch')->orderby('id', 'ASC')->take($limit)->get();

            if(count($logs)>0):
                try {
                    DB::beginTransaction();

                    $batch_no = preg_replace("/[^A-Za-z0-9]/", '', microtime());

                    $filename = '/csv/byterevenue-messages-' . $batch_no . '.csv';

                    $csvContent = '';

                    $csvContent .= implode(',', ['UID','Phone', 'Subject', 'Text']) . "\n";

                    $current_campaign_id = 0;
                    $message = null;
                    $campaign_service = new CampaignService();
                    foreach ($logs as $log) {

                        if ($log->campaign_id != $current_campaign_id) {
                            if ($log->campaign_id == 0) {
                                $message = $log->message;
                                $campaign = $message->campaign;

                            } else {
                                $campaign = $log->campaign;
                                $current_campaign_id = $log->campaign->id;
                                $message = $log->message;
                            }
                        }
                        if ($message) {
                            // allocate a uniq id to the campaign if it doesnt have one already
                            // check if there an existing URL for this campaign with the same domain
                            $campaign_short_url = CampaignShortUrl::select()->where('campaign_id', $campaign->id)->where('url_shortener', 'like', '%'.$url_shortener.'%')->orderby('id', 'desc')->first();

                            // if yes - use that

                            if($campaign_short_url){
                                $alias_for_campaign = explode('?', explode(DIRECTORY_SEPARATOR,  $campaign_short_url->url_shortener)[1])[0];
                            }else{
                                $alias_for_campaign = uniqid();
                            }

                            $generated_url = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign, $log->id);

                            $log->message_body = $message->getParsedMessage($generated_url);


                            $campaign_key = $campaign->id . '';
                            if ($campaign && isset($unique_campaign_map[$campaign_key]) == false) {
                                $unique_campaigns->add(['campaign'=>$campaign, 'alias'=>$alias_for_campaign]);
                                $unique_campaign_map[$campaign_key] = true;
                            }
                        }
                        $log->save();
                        // generate message for each log from the message spintax
                        // replace with a url - use the provided short url
                        // save the log and generate a batch for the log based upon the current microtime and mark all logs processed
                        // with that batch number
                        // name the csv using the same batch number

                        $csvContent .= implode(',', [$log->id, $log->recipient_phone, '', $log->message_body]) . "\n";
                    }
                    $download_me = env('DO_SPACES_ENDPOINT') . $filename;
                    Storage::disk('spaces')->put($filename, $csvContent);

                    BatchFile::create(['filename' => $filename,
                        'path' => env('DO_SPACES_ENDPOINT') . $filename,
                        'number_of_entries' => count($logs),
                        'campaign_id' => $log->campaign_id]);


                    BroadcastLog::where('id', '<=', BroadcastLog::where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get()->last()->id)
                        ->update(['is_downloaded_as_csv' => 1, 'batch' => $batch_no]);
                    $unique_campaigns->each(function ($itemCollection) use ($url_shortener, $unique_campaign_map, $campaign_service, $log, $domain_id) {
                        $alias_for_campaign = $itemCollection['alias'];
                        $item = $itemCollection['campaign'];

                        $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);
                        $url_for_campaign = $item->message?->target_url;
                        // maintain a record of short urls created against a campaign
                        // if a short url has been created against a campaign - do not create a new one, use the existing one
                        // else create a new one
                        if (!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($item->id, $url_for_keitaro)) {

                            $response_campaign = $campaign_service->createCampaignOnKeitaro($alias_for_campaign, $item->title, $item->keitaro_group_id, $domain_id);
                            $response_flow = $campaign_service->createFlowOnKeitaro($response_campaign['id'], $item->title, $url_for_campaign);
                            $this->campaignShortUrlRepository->create([
                                'campaign_id' => $item->id,
                                'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                                'flow_id' => $response_flow['id'],
                                'response' => @json_encode($response_flow),
                                'keitaro_campaign_id' => $response_campaign['id'],
                                'keitaro_campaign_response' => @json_encode($response_campaign),
                                'campaign_alias' => $alias_for_campaign,
                            ]);

                        }
                    });
                    DB::commit();
                }
                catch (RequestException $exception) {
                    DB::rollBack();
                    var_dump($exception);
                    die();
                    return redirect()->route('jobs.index')->with('error', $exception->getMessage());
                } catch (\Exception $exception) {
                    DB::rollBack();
                    report($exception);
                    die();
                    return redirect()->route('jobs.index')->with('error', 'Error Call Keitaro');
                }

            endif;

        }


        $directory = storage_path('app/csv/');
        $files = BatchFile::select()->orderby('id', 'desc')->paginate(15);

        // get count of all messages in the queue
        $params['total_in_queue'] = BroadcastLog::select()->count();
        $params['files'] = $files;
        $params['download_me'] = $download_me;
        $params['urlShorteners'] = $urlShorteners;
        $params['total_not_downloaded_in_queue'] = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();
        return view('jobs.index', compact('params'))->with('success', 'Campaign created successfully.');
    }

    public function downloadFile($filename)
    {
        $filePath = storage_path('app/csv/' . $filename);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
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
