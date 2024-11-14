<?php

namespace App\Http\Controllers;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\SettingUploadSendDataRequest;
use App\Models\Setting;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Setting\SettingRepositoryInterface;
use App\Services\Settings\SettingService;
use App\Trait\CSVReader;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    use CSVReader;
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected BlackListNumberRepositoryInterface $blackListNumberRepository,
        private SettingService $settingService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function indexApi(Request $request)
    {
        $response = $this->settingService->getAll($request);
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeApi(Request $request)
    {
        $response = $this->settingService->store($request);
        if (isset($response['errors'])) {
            return response()->json($response, 400);
        }
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateApi($id, Request $request)
    {
        $response = $this->settingService->update($id, $request);
        if (isset($response['errors'])) {
            return response()->json($response, 400);
        }
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteApi($id)
    {
        $response = $this->settingService->delete($id);
        return response()->json($response);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'count' => request('count', 5),
        ];
        $list = Setting::latest()->paginate($request->count);
        return view('settings.index', compact('list', 'filter'));
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
        $id = $setting->id;
        $request->validate([
            'name' => "required|unique:settings,name,$id|string|min:1|max:255",
            'value' => "required|min:1",
            'label' => "nullable|string|min:1|max:255",
        ]);
        $value = $request->value;
        if (is_array($value)) {
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

    public function uploadSendData(SettingUploadSendDataRequest $request)
    {
        [$result, $number_of_updated_rows] = $this->settingService->uploadSendData($request);

        if ($result) {
            return redirect()->route('messages.uploadMessageSendDataIndex')
                ->with('success', "Send Data Updated for $number_of_updated_rows Message");
        }

        return redirect()->route('messages.uploadMessageSendDataIndex')->with('error', 'error parse csv');
    }

    /**
     * @param SettingUploadSendDataRequest $request
     *
     * @return JsonResponse
     */
    public function uploadSendDataApi(SettingUploadSendDataRequest $request)
    {
        [$result, $number_of_updated_rows] = $this->settingService->uploadSendData($request);

        if ($result) {
            return $this->responseSuccess(
                ['updated_rows' => $number_of_updated_rows],
                "Send Data Updated for $number_of_updated_rows Message"
            );
        }

        return $this->responseError('Error parse csv');
    }

    /**
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function uploadBlackListNumberIndex()
    {
        return view('settings.upload_black_list_number');
    }

    /**
     * @param SettingUploadSendDataRequest $request
     *
     * @return RedirectResponse
     */
    public function uploadBlackListNumber(SettingUploadSendDataRequest $request)
    {
        [$result, $error] = $this->settingService->uploadBlacklistNumber($request);

        if (!$result) {
            if ($error === 'parse_error') {
                return redirect()->route('messages.uploadBlockNumberIndex')
                    ->with('error', 'error parse csv');
            }

            if ($error === 'phone_number') {
                return redirect()->route('messages.uploadBlackListNumberIndex')
                    ->with('error', "first column should be phone_number");
            }
        }

        return redirect()->route('messages.uploadBlackListNumberIndex')
            ->with('success', "Update Was Successful");
    }

    /**
     * @param SettingUploadSendDataRequest $request
     *
     * @return JsonResponse
     */
    public function uploadBlackListNumberApi(SettingUploadSendDataRequest $request)
    {
        [$result, $error] = $this->settingService->uploadBlacklistNumber($request);

        if (!$result) {
            if ($error === 'parse_error') {
                return $this->responseError('Error parse csv');
            }

            if ($error === 'phone_number') {
                return $this->responseError('First column should be phone_number');
            }
        }

        return $this->responseSuccess([], 'Update Was Successful');
    }
}
