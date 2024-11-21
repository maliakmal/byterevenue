<?php

namespace App\Services\Settings;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Requests\SettingUploadSendDataRequest;
use App\Models\Setting;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Setting\SettingRepositoryInterface;
use App\Trait\CSVReader;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Validator;

class SettingService
{
    use CSVReader;

    /**
     * @param SettingRepositoryInterface $settingRepository
     * @param BroadcastLogRepositoryInterface $broadcastLogRepository
     * @param BlackListNumberRepositoryInterface $blackListNumberRepository
     */
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected BlackListNumberRepositoryInterface $blackListNumberRepository
    ){}

    public function getAll(Request $request)
    {
        $settings = Setting::latest();
        $perPage = request('count', 15);
        $list = $settings->paginate($perPage);
        return $list;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'unique:settings,name', 'string', 'min:1', 'max:255'],
            'value' => ['required','string','min:1'],
            'label' => ['nullable','string','min:1','max:255'],
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        $data = $validator->validated();
        $setting = $this->settingRepository->create([
            'name' => $data ['name'],
            'value' => $data ['value'],
            'label' => $data ['label'],
        ]);
        return $setting;
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','unique:settings,name,'.$id,'string','min:1','max:255'],
            'value' => ['required','min:1'],
            'label' => ['required','nullable','string','min:1','max:255'],
        ]);
        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        $data = $validator->validated();
        $value = $data['value'];

        if (is_array($value)) {
            $value = collect($value)->whereNotNull()->toArray();
            $value = json_encode(array_values($value));
        }

        Setting::whereId($id)->update($data);

        return ['message' => 'Setting updated successfully.'];
    }

    public function delete($id)
    {
        Setting::whereId($id)->delete();
        return ['message' => 'Setting deleted successfully.'];
    }

    /**
     * @param SettingUploadSendDataRequest $request
     *
     * @return array
     */
    public function uploadSendData(SettingUploadSendDataRequest $request)
    {
        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        if ($request->has_header == 'no') {
            $content = "UID\n" . $content;
        }
        $csv = $this->csvToCollection($content);
        if (!$csv) {
            return [false, 0];
        }
        $message_ids = $csv->pluck('UID')->toArray();

        $rows = $this->broadcastLogRepository->updateWithIDs($message_ids, [
            'sent_at' => Carbon::now(),
            'is_sent' => true,
            'status' => BroadcastLogStatus::SENT,
        ]);

        return [true, $rows];
    }

    /**
     * @param $request
     *
     * @return array
     */
    public function uploadBlacklistNumber($request)
    {
        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        if ($request->has_header == 'no') {
            $content = "phone_number\n" . $content;
        }
        $csv = $this->csvToCollection($content);
        if (!$csv) {
            return [false, 'parse_error'];
        }
        if (isset($csv->first()['phone_number']) == false) {
            return [false, 'phone_number'];
        }
        $data = $csv->toArray();
        $this->blackListNumberRepository->upsertPhoneNumber($data);

        return [true, ''];
    }
}
