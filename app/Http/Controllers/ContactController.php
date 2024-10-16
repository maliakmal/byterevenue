<?php

namespace App\Http\Controllers;

use App\Models\AreaCode;
use App\Models\Contact;
use App\Services\AreaCode\AreaCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    private  AreaCodeService $areaCodeService;

    public function __construct()
    {
        //$this->authorizeResource(Contact::class);
        $this->areaCodeService = new AreaCodeService();
    }

    public function index()
    {
        $user       = auth()->user();
        $perPage    = request('per_page', 12);
        $name       = request('name');
        $area_code  = request('area_code', '');
        $phone      = request('phone', '');

        $filter_phone = $area_code ?: '%';
        $filter_phone .= ($phone ?: '') .'%';

        $contacts = $user->hasRole('admin') ? Contact::query() : Contact::where('user_id', $user->id);
        $contacts = $contacts->withCount(['campaigns', 'sentMessages', 'recipientLists', 'blackListNumber'])
            ->when($phone || $area_code, function ($query) use ($filter_phone) {
                return $query->where('phone', 'like', $filter_phone);
            })
            ->when($name, function ($query, $name) {
                return $query->where('name', $name);
            });

        $contacts = $contacts->orderBy('id', 'desc')->paginate($perPage);

        if ('json' === request()->input('output')) {
            return response()->success(null, $contacts);
        }

        $area_data = [
            'provinces' => $this->areaCodeService->getAllProvinces('true'),
            //'cities'  => $this->areaCodeService->getAllCities('true') // legacy data
        ];

        return view('contacts.index', compact('contacts', 'area_data'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        auth()->user()->contacts()->create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact created successfully.');
    }

    public function show(Contact $dataSource)
    {
        return view('contacts.show', compact('dataSource'));
    }

    public function edit(Contact $dataSource)
    {
        return view('contacts.edit', compact('dataSource'));
    }

    public function update(Request $request, Contact $dataSource)
    {
        $request->validate([
            'name'  => 'string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $dataSource->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $dataSource)
    {
        $dataSource->delete();

        return redirect()->route('data-source.index')->with('success', 'Contact deleted successfully.');
    }
}
