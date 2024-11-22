<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientApiController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/clients",
     *     summary="Get a list of clients",
     *     tags={"Clients"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @return JsonResponse
     */
    public function index()
    {
        $clients = Client::latest()->paginate(10);

        return $this->responseSuccess($clients);
    }

    /**
     * @OA\Post(
     *     path="/clients",
     *     summary="Store a new client",
     *     tags={"Clients"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Client Name"),
     *             @OA\Property(property="email", type="string", example="client@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     )
     * )
     * @param ClientStoreRequest $request
     * @return JsonResponse
     */
    public function store(ClientStoreRequest $request)
    {
        $client = Client::create(
            [
                'user_id' => auth()->id(),
                'name' => $request->name,
                'email' => $request->email,
            ]
        );

        return $this->responseSuccess($client, 'Client created successfully.');
    }

    /**
     * @OA\Get(
     *     path="/clients/{id}",
     *     summary="Get a client",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Client ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        return $this->responseSuccess(Client::findOrFail($id));
    }

    /**
     * @OA\Put(
     *     path="/clients/{id}",
     *     summary="Update a client",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Client ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Client Name"),
     *             @OA\Property(property="email", type="string", example="updatedclient@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     )
     * )
     * @param int $id
     * @param ClientUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, ClientUpdateRequest $request)
    {
        $client = Client::findOrFail($id);

        if (!$client) {
            return $this->responseError([], 'Client not found.', 404);
        }

        $client->update($request->validated());

        return $this->responseSuccess($client, 'Client updated successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/clients/{id}",
     *     summary="Delete a client",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Client ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Client deleted successfully.")
     *         )
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        Client::findOrFail($id)->delete();

        return $this->responseSuccess(message: 'Client deleted successfully.');
    }
}
