<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactStoreRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Models\Contact;
use App\Services\AreaCode\AreaCodeService;
use App\Services\Contact\ContactService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private AreaCodeService $areaCodeService,
        private ContactService $contactService
    ) {}

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page'   => 'sometimes|nullable|integer|min:1|max:100',
            'name'       => 'sometimes|nullable|string',
            'area_code'  => 'sometimes|nullable|string',
            'status'     => 'sometimes|nullable|integer',
            'phone'      => 'sometimes|nullable|string',
            'sort_by'    => 'sometimes|nullable|string|in:id,name,email,phone',
            'sort_order' => 'sometimes|nullable|string|in:asc,desc',
        ]);

        $data = [];
        $data['user']       = auth()->user();
        $data['perPage']    = $request->input('per_page', 15);
        $data['name']       = $request->input('name');
        $data['area_code']  = $request->input('area_code', '');
        $data['status']     = intval($request->input('status',-1));
        $data['phone']      = $request->input('phone', '');
        $data['sortBy']     = $request->input('sort_by', 'id');
        $data['sortOrder']  = $request->input('sort_order', 'desc');

        $contacts = $this->contactService->getContacts($data);

        if ('json' === $request->input('output')) {
            return response()->success(null, $contacts);
        }

        $area_data = [
            'provinces' => $this->areaCodeService->getAllProvinces(true),
            //'cities'  => $this->areaCodeService->getAllCities('true') // legacy data
        ];

        return view('contacts.index', compact('contacts', 'area_data'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    /**
     * @param ContactStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(ContactStoreRequest $request)
    {
        auth()->user()->contacts()->create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact created successfully.');
    }

    /**
     * @param Contact $dataSource
     *
     * @return View
     */
    public function show(Contact $dataSource)
    {
        return view('contacts.show', compact('dataSource'));
    }

    /**
     * @param Contact $dataSource
     *
     * @return View
     */
    public function edit(Contact $dataSource)
    {
        return view('contacts.edit', compact('dataSource'));
    }

    /**
     * @param ContactUpdateRequest $request
     * @param Contact $dataSource
     *
     * @return RedirectResponse
     */
    public function update(ContactUpdateRequest $request, Contact $dataSource)
    {
        $dataSource->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact updated successfully.');
    }

    /**
     * @param Contact $dataSource
     *
     * @return RedirectResponse
     */
    public function destroy(Contact $dataSource)
    {
        $dataSource->delete();

        return redirect()->route('data-source.index')->with('success', 'Contact deleted successfully.');
    }

    public function contactsInfo(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array',
        ]);

        return response()->json([
            'data' => $this->contactService->getInfo($request->get('contacts')),
        ]);
    }
}
