<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::where('user_id', auth()->id())->latest()->paginate(5);
        return view('clients.index', compact('clients'));
    }

    /**
     * @return JsonResponse
     */
    public function indexApi()
    {
        return $this->responseSuccess(
            Client::where('user_id', auth()->id())
                ->latest()
                ->paginate(5)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientStoreRequest $request)
    {
        auth()->user()->clients()->create($request->validated());

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * @param ClientStoreRequest $request
     *
     * @return JsonResponse
     */
    public function storeApi(ClientStoreRequest $request)
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
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function showApi(int $id)
    {
        return $this->responseSuccess(Client::findOrFail($id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientUpdateRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * @param int $id
     * @param ClientUpdateRequest $request
     *
     * @return JsonResponse
     */
    public function updateApi(int $id, ClientUpdateRequest $request)
    {
        $client = Client::findOrFail($id);

        if (!$client) {
            return $this->responseError([], 'Client not found.', 404);
        }

        $client->update($request->validated());

        return $this->responseSuccess($client, 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        Client::findOrFail($id)->delete();

        return $this->responseSuccess([], 'Client deleted successfully.');
    }
}
