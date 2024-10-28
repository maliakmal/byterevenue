<?php

namespace App\Services\Settings;

use App\Models\Setting;
use App\Repositories\Contract\Setting\SettingRepositoryInterface;
use Illuminate\Http\Request;
use Validator;

class SettingService
{
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
    ){}

    public function getAll(Request $request)
    {
        $settings = Setting::latest();
        $perPage = $request->filled('count') ? $request->count : 15;
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
}
