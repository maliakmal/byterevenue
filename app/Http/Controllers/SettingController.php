<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Repositories\Contract\Setting\SettingRepositoryInterface;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        protected SettingRepositoryInterface $settingRepository
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
                'key' => 'required|unique:settings,key|string|min:1|max:255',
                'value' => 'required|string|min:1|max:255',
            ]);
            $inputs = $request->all();
            $setting = $this->settingRepository->create([
                'key' => $inputs['key'],
                'value' => $inputs['value'],
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
                'key' => "required|unique:settings,key,$id|string|min:1|max:255",
                'value' => "required|string|min:1|max:255",
            ]);

            $setting->key = $request->key;
            $setting->value = $request->value;
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
    }
