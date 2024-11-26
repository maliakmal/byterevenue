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
    public function index()
    {
        $user       = auth()->user();
        $perPage    = request('per_page', 12);
        $name       = request('name');
        $area_code  = request('area_code', '');
        $phone      = request('phone', '');

        $contacts = $this->contactService->getContacts($user, $perPage, $name, $area_code, $phone);

        if ('json' === request()->input('output')) {
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
