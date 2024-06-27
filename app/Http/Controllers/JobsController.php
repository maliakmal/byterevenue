<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\BroadcastLog;
use App\Models\BatchFile;
use App\Models\UrlShortener;
use Illuminate\Support\Facades\Storage;
use File;

class JobsController extends Controller
{
    //

    public function index(Request $request){

        $download_me = null;
        $urlShorteners = UrlShortener::all();
        if ($request->isMethod('post')) {
            
            $limit = $request->number_messages;
            $url_shortener = $request->url_shortener;

            //$logs = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get();
            $logs = BroadcastLog::select()->whereNull('batch')->orderby('id', 'ASC')->take($limit)->get();

            if(count($logs)>0):

                $batch_no = preg_replace("/[^A-Za-z0-9]/", '', microtime());

                $filename = '/csv/byterevenue-messages-'.$batch_no.'.csv';

                $csvContent = '';
                
                $csvContent .= implode(',', ['Phone', 'Subject', 'Text']) . "\n";

                $current_campaign_id = 0;
                $message = null;
                foreach($logs as $log){

                    if($log->campaign_id!=$current_campaign_id){
                        if($log->campaign_id == 0){
                            $message = $log->message;
                            $campaign = $message->campaign;
    
                        }else{
                            $campaign = $log->campaign;
                            $current_campaign_id = $log->campaign->id;
                            $message = $log->message;
                        }
                    }
                    if($message){
                        $log->message_body = $message->getParsedMessage($campaign->generateTrackableUrl($url_shortener, [$log->id]));
                    }
                    $log->save();
                // generate message for each log from the message spintax
                // replace with a url - use the provided short url
                // save the log and generate a batch for the log based upon the current microtime and mark all logs processed 
                // with that batch number
                // name the csv using the same batch number

                    $csvContent .= implode(',', [$log->recipient_phone, '', $log->message_body]) . "\n";
                }
                $download_me = env('DO_SPACES_ENDPOINT').$filename;
                Storage::disk('spaces')->put($filename, $csvContent);

                BatchFile::create(['filename' => $filename,
                    'path' =>env('DO_SPACES_ENDPOINT').$filename,
                    'number_of_entries'=>count($logs),
                    'campaign_id'=>$log->campaign_id]);
                

                BroadcastLog::where('id', '<=', BroadcastLog::where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get()->last()->id)
                ->update(['is_downloaded_as_csv' => 1, 'batch'=>$batch_no]);


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


    
}
