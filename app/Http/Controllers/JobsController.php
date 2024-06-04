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

            $logs = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get();

            if(count($logs)>0):

                $filename = '/csv/byterevenue-messages-'.time().'.csv';

                $csvContent = '';
                
                $csvContent .= implode(',', ['Phone', 'Subject', 'Text']) . "\n";
                foreach($logs as $log){
                    $csvContent .= implode(',', [$log->recipient_phone, '', $log->message_body]) . "\n";
                }
                $download_me = env('DO_SPACES_ENDPOINT').$filename;
                Storage::disk('spaces')->put($filename, $csvContent);

                BatchFile::create(['filename' => $filename,
                    'path' =>env('DO_SPACES_ENDPOINT').$filename,
                    'number_of_entries'=>count($logs),
                    'broadcast_batch_id'=>$log->broadcast_batch_id]);
                
                BroadcastLog::where('id', '<=', BroadcastLog::where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get()->last()->id)
                ->update(['is_downloaded_as_csv' => 1]);


            endif;

        }


        $directory = storage_path('app/csv/');
        $files = BatchFile::select()->orderby('id', 'asc')->paginate(10);

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
