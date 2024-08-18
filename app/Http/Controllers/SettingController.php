<?php

namespace App\Http\Controllers;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Models\Setting;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Setting\SettingRepositoryInterface;
use App\Trait\CSVReader;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use CSVReader;
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository
    )
    {
    }

    /**
         * Display a listing of the resource.
         */
        public function index(Request $request)
        {
            $filter = array(
                'count'=> request('count')?request('count'):5,
            );
            $list = Setting::latest()->paginate($request->count);
            return view('settings.index', compact('list', 'filter' ));
        }

        /**
         * Show the form for creating a new resource.
         */
        public function create()
        {
            return view('settings.create');
        }

        /**
         * Store a newly created resource in storage.
         */
        public function store(Request $request)
        {
            $request->validate([
                'name' => 'required|unique:settings,name|string|min:1|max:255',
                'value' => 'required|string|min:1',
                'label' => 'nullable|string|min:1|max:255',
            ]);
            $inputs = $request->all();
            $setting = $this->settingRepository->create([
                'name' => $inputs['name'],
                'value' => $inputs['value'],
                'label' => $inputs['label'],
            ]);
            return redirect()->route('settings.index', $setting)->with('success', 'Setting created successfully.');
        }

        /**
         * Show the form for editing the specified resource.
         */
        public function edit(Setting $setting)
        {
            return view('settings.edit', compact('setting'));
        }

        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, Setting $setting)
        {
            $request->all();
            $id = $setting->id;
            $request->validate([
                'name' => "required|unique:settings,name,$id|string|min:1|max:255",
                'value' => "required|min:1",
                'label' => "nullable|string|min:1|max:255",
            ]);
            $value = $request->value;
            if(is_array($value)){
                $value = collect($value)->whereNotNull()->toArray();
                $value = json_encode(array_values($value));
            }

            $setting->name = $request->name;
            $setting->value = $value;
            $setting->label = $request->label;
            $setting->save();
            return redirect()->route('settings.index', $setting)->with('success', 'Setting Updated successfully.');
        }

        /**
         * Remove the specified resource from storage.
         */
        public function destroy(Setting $setting)
        {
            $setting->delete();
            return redirect()->route('settings.index')->with('success', 'Setting deleted successfully.');
        }

    public function uploadSendDataIndex()
    {
        return view('settings.upload_message_send_data');
    }
    public function uploadSendData(Request $request)
    {
        $max_allowed_csv_upload_file = config('app.csv.upload_max_size_allowed');
        $request->validate([
            'file' => "required|max:$max_allowed_csv_upload_file",
            'has_header' => "required|in:yes,no",
        ]);
        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        if($request->has_header == 'no'){
            $content = "UID\n".$content;
        }
        $csv = $this->csvToCollection($content);
        if(!$csv){
            return redirect()->route('messages.uploadMessageSendDataIndex')->with('error', 'error parse csv');
        }
        $message_ids = $csv->pluck('UID')->toArray();
        $number_of_updated_rows = $this->broadcastLogRepository->updateWithIDs($message_ids, [
            'sent_at' => Carbon::now(),
            'is_sent' => true,
            'status' => BroadcastLogStatus::SENT,
        ]);
        return redirect()->route('messages.uploadMessageSendDataIndex')->with('success', "Send Data Updated for $number_of_updated_rows Message");
    }
}
