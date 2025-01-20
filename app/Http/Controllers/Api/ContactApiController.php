<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ContactStoreRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Models\Contact;
use App\Services\Contact\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactApiController extends ApiController
{
    /**
     * @param ContactService $contactService
     */
    public function __construct(
        private ContactService $contactService
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $contacts = $this->contactService->getContacts($request);

        return $this->responseSuccess($contacts);
    }

    /**
     * @param ContactStoreRequest $request
     * @return JsonResponse
     */
    public function store(ContactStoreRequest $request): JsonResponse
    {
        $intPhone = (int)preg_replace('/[^0-9]/', '', $request->phone);
        $intPhone = $intPhone ?: null;

        $contact = auth()->user()->contacts()->create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $intPhone,
        ]);

        return $this->responseSuccess($contact);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $contact = Contact::when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->find($id);

        if (!$contact) {
            return $this->responseError([], 'Contact not found.', 404);
        }

        return $this->responseSuccess($contact);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $contact = Contact::when(!auth()->user()->hasRole('admin'), function ($query) {
            return $query->where('user_id', auth()->id());
        })
            ->find($id);

        if (!$contact) {
            return $this->responseError([], 'Contact not found.', 404);
        }

        return $this->responseSuccess($contact);
    }

    /**
     * @param int $id
     * @param ContactUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, ContactUpdateRequest $request): JsonResponse
    {
        // $$$ зачем список черных номеров?
        $contact = Contact::withCount(['blackListNumber'])->find($id);

        if (!$contact) {
            return $this->responseError([], 'Contact not found.', 404);
        }

        $intPhone = (int)preg_replace('/[^0-9]/', '', $request->phone);
        $intPhone = $intPhone ?: null;

        $contact->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $intPhone,
        ]);
        // $$$ зачем это возвращать?
        $info = $this->contactService->getInfo([$id]);
        $contact['sent_count'] = $info['sent'];
        $contact['campaigns_count'] = $info['campaigns'];

        return $this->responseSuccess($contact);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $contact = Contact::when(!auth()->user()->hasRole('admin'), function ($query) {
            return $query->where('user_id', auth()->id());
        })
            ->find($id);

        if ($contact && $contact->delete()) {
            return $this->responseSuccess([], 'Contact deleted successfully.');
        }

        return $this->responseError([], 'Failed to delete the contact.', 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function contactsInfo(Request $request): JsonResponse
    {
        $request->validate([
            'contacts' => 'required|array',
        ]);

        return $this->responseSuccess(
            $this->contactService->getInfo($request->get('contacts'))
        );
    }
}
