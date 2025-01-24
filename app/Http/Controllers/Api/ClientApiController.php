<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

// This controller is not use anywhere
class ClientApiController extends ApiController
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $clients = Client::latest()->paginate(10);

        return $this->responseSuccess($clients);
    }

    /**
     * @param ClientStoreRequest $request
     * @return JsonResponse
     */
    public function store(ClientStoreRequest $request): JsonResponse
    {
        $client = Client::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return $this->responseSuccess($client, 'Client created successfully.');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $client = Client::findOrFail($id);

        return $this->responseSuccess($client);
    }

    /**
     * @param int $id
     * @param ClientUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, ClientUpdateRequest $request): JsonResponse
    {
        $client = Client::findOrFail($id);

        if (!$client) {
            return $this->responseError([], 'Client not found.', 404);
        }

        $client->update($request->validated());

        return $this->responseSuccess($client, 'Client updated successfully.');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        Client::findOrFail($id)->delete();

        return $this->responseSuccess(message: 'Client deleted successfully.');
    }
}
