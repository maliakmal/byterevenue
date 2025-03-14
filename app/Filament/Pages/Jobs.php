<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\BroadcastLog;
use Illuminate\Support\Facades\Storage;
use File;

class Jobs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.jobs';

    public function index(Request $request){

        $download_me = null;
        if ($request->isMethod('post')) {
            
            $limit = $request->number_messages;
            $url_shortener = $request->url_shortener;

            $logs = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get();

            if(count($logs)>0):

                $filename = 'byterevenue-messages-'.time().'.csv';
                $filePath = storage_path('app/csv/'.$filename);

                $file = fopen($filePath, 'w');
                fputcsv($file, ['Phone', 'Subject', 'Text']);

                foreach($logs as $log){
                    fputcsv($file, [$log->recipient_phone, '', $log->message_body]);
                }
                fclose($file);
                $download_me = $filename;
                BroadcastLog::where('id', '<=', BroadcastLog::where('is_downloaded_as_csv', 0)->orderby('id', 'ASC')->take($limit)->get()->last()->id)
                ->update(['is_downloaded_as_csv' => 1]);
            endif;

        }
        $directory = storage_path('app/csv/');
        $all_files = Storage::disk('csv')->files();

        $files = [];
        foreach ($all_files as $file) {


            $lastModifiedTime = Carbon::createFromTimestamp(Storage::disk('csv')->lastModified($file));
            $files[] = [
                'name' => basename($file),
                'created_at' => $lastModifiedTime->diffForHumans()
            ];
        }

        // get count of all messages in the queue
        $params['total_in_queue'] = BroadcastLog::select()->count();
        $params['files'] = $files;
        $params['download_me'] = $download_me;
        $params['total_not_downloaded_in_queue'] = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();
    }

}
