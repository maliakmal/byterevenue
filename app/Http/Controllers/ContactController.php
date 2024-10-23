<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\StoreContactRequest;
use App\Models\AreaCode;
use App\Models\Contact;
use App\Services\AreaCode\AreaCodeService;
use App\Services\Contact\ContactService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends ApiController
{
    private AreaCodeService $areaCodeService;
    private ContactService $contactService;

    /**
     * @param AreaCodeService $areaCodeService
     * @param ContactService $contactService
     */
    public function __construct(
        AreaCodeService $areaCodeService,
        ContactService $contactService
    ) {
        //$this->authorizeResource(Contact::class);
        $this->areaCodeService = $areaCodeService;
        $this->contactService = $contactService;
    }

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
            'provinces' => $this->areaCodeService->getAllProvinces('true'),
            //'cities'  => $this->areaCodeService->getAllCities('true') // legacy data
        ];

        return view('contacts.index', compact('contacts', 'area_data'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexApi(Request $request)
    {
        $user       = auth()->user();
        $perPage    = $request->input('per_page', 12);
        $name       = $request->input('name');
        $area_code  = $request->input('area_code', '');
        $phone      = $request->input('phone', '');

        $contacts = $this->contactService->getContacts($user, $perPage, $name, $area_code, $phone);
        $areaData = [
            'provinces' => $this->areaCodeService->getAllProvinces('true'),
        ];

        return $this->responseSuccess([
            'contacts' => $contacts,
            'area_data' => $areaData,
        ]);
    }

    public function create()
    {
        return view('contacts.create');
    }

    /**
     * @param StoreContactRequest $request
     *
     * @return RedirectResponse
     */
    public function store(StoreContactRequest $request)
    {
        auth()->user()->contacts()->create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact created successfully.');
    }

    /**
     * @param StoreContactRequest $request
     *
     * @return JsonResponse
     */
    public function storeApi(StoreContactRequest $request)
    {
        $contact = auth()->user()->contacts()->create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return $this->responseSuccess($contact);
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
     * @param int $id
     *
     * @return JsonResponse
     */
    public function showApi(int $id)
    {
        return $this->responseSuccess(Contact::find($id));
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
     * @param int $id
     *
     * @return JsonResponse
     */
    public function editApi(int $id)
    {
        return $this->responseSuccess(Contact::find($id));
    }

    /**
     * @param StoreContactRequest $request
     * @param Contact $dataSource
     *
     * @return RedirectResponse
     */
    public function update(StoreContactRequest $request, Contact $dataSource)
    {
        $dataSource->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact updated successfully.');
    }

    /**
     * @param int $id
     * @param StoreContactRequest $request
     *
     * @return JsonResponse
     */
    public function updateApi(int $id, StoreContactRequest $request)
    {
        $contact = Contact::find($id);
        $contact->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return $this->responseSuccess($contact);
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

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        $contact = Contact::find($id)->delete();

        return $this->responseSuccess($contact);
    }
}
