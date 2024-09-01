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
        $perPage = \request()->input('per_page', 12);
        if(auth()->user()->hasRole('admin')) {
            $contacts = Contact::select();
        }
        else {
            $contacts = auth()->user()->contacts();
        }
        $contacts = $contacts->withCount(['campaigns', 'sentMessages', 'recipientLists', 'blackListNumber']);
        $area_code = \request('area_code', '');
        $phone = \request('phone', '');
        if(!empty($area_code) || !empty($phone)){
            $filter_phone = $area_code.$phone.'%';
            if(empty($area_code)){
                $filter_phone = '%'.$filter_phone;
            }
            $contacts = $contacts->where('phone', 'like', $filter_phone);
        }
        if(!empty(\request('name'))){
            $contacts = $contacts->where('name', \request('name'));
        }
        $contacts = $contacts->orderby('id', 'desc')->paginate($perPage);
        if(\request()->input('output') == 'json'){
            return response()->success(null, $contacts);
        }
        $area_data = $this->areaCodeService->getAreaData();
        return view('contacts.index', compact('contacts', 'area_data'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        auth()->user()->contacts()->create([
            'name' => $request->name,
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
            'name' => 'string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $dataSource->update([
            'name' => $request->name,
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
