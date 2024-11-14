<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Requests\ContactStoreRequest;
use App\Models\Contact;
use App\Services\AreaCode\AreaCodeService;
use App\Services\Contact\ContactService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'provinces' => $this->areaCodeService->getAllProvinces(true),
            //'cities'  => $this->areaCodeService->getAllCities('true') // legacy data
        ];

        return view('contacts.index', compact('contacts', 'area_data'));
    }

    /**
     * @OA\Get(
     *     path="/data-source",
     *     summary="Get contacts",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Number of contacts per page"
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Contact name"
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Area code"
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Phone number"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="contacts", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="area_data", type="object")
     *         )
     *     )
     * )
     * @param Request $request
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
            'provinces' => $this->areaCodeService->getAllProvinces(true),
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
     * @OA\Post(
     *     path="/data-source",
     *     summary="Store a new contact",
     *     tags={"Contacts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="phone", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     )
     * )
     * @param ContactStoreRequest $request
     * @return JsonResponse
     */
    public function storeApi(ContactStoreRequest $request)
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
     * @OA\Get(
     *     path="/data-source/{id}",
     *     summary="Get a contact",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     )
     * )
     * @param int $id
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
     * @OA\Get(
     *     path="/data-source/{id}/edit",
     *     summary="Edit a contact",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function editApi(int $id)
    {
        return $this->responseSuccess(Contact::find($id));
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
     * @OA\Put(
     *     path="/data-source/{id}",
     *     summary="Update a contact",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="phone", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     )
     * )
     * @param int $id
     * @param ContactUpdateRequest $request
     * @return JsonResponse
     */
    public function updateApi(int $id, ContactUpdateRequest $request)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return $this->responseError([], 'Contact not found.', 404);
        }

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
     * @OA\Delete(
     *     path="/data-source/{id}",
     *     summary="Delete a contact",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Contact deleted successfully.")
     *         )
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        $contact = Contact::find($id)->delete();

        return $this->responseSuccess($contact);
    }

    /**
     * @OA\Post(
     *     path="/data-source/info",
     *     summary="Get contacts info",
     *     tags={"Contacts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="contacts", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function contactsInfo(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array',
        ]);

        return $this->responseSuccess(
            $this->contactService->getInfo($request->get('contacts'))
        );
    }
}
