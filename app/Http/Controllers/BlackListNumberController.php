<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\BlacklistNumberStoreRequest;
use App\Services\BlacklistNumber\BlacklistNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BlackListNumber;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use App\Repositories\Contract\Contact\ContactRepositoryInterface;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;

class BlackListNumberController extends ApiController
{
    public function __construct(
        protected BlackListNumberRepositoryInterface $blackListNumberRepository,
        protected ContactRepositoryInterface $contactRepository,
        protected BlacklistNumberService $blacklistNumberService
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'count'=> request('count', 5),
        ];
        $list = $this->blackListNumberRepository->paginate($request->count);
        return view('black_list_number.index', compact('list', 'filter' ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAPi(Request $request)
    {
        $filter = [
            'count'=> request('count', 5),
        ];
        $list = $this->blackListNumberRepository->paginate($request->count);

        return $this->responseSuccess([
            'list' => $list,
            'filter' => $filter,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('black_list_number.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlacklistNumberStoreRequest $request)
    {
        $this->blacklistNumberService->store($request->validated());

        return redirect()->route('black-list-numbers.index')->with('success', 'The Item created successfully.');
    }

    /**
     * @param BlacklistNumberStoreRequest $request
     *
     * @return JsonResponse
     */
    public function storeApi(BlacklistNumberStoreRequest $request)
    {
        $blacklistNumber = $this->blacklistNumberService->store($request->validated());

        return $this->responseSuccess($blacklistNumber, 'The Item created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlackListNumber $blackListNumber)
    {
        return view('black_list_number.edit', compact('blackListNumber'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlackListNumber $blackListNumber)
    {
        $id = $blackListNumber->id;
        $request->validate([
            'phone_number' => "required|unique:black_list_numbers,phone_number,$id|string|min:1|max:255",
        ]);
        $blackListNumber->phone_number = $request->phone_number;
        $blackListNumber->save();
        return redirect()->route('black-list-numbers.index', $blackListNumber)->with('success', 'Item Updated successfully.');
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateApi(int $id, Request $request)
    {
        $blackListNumber = BlackListNumber::findOrFail($id);

        $request->validate([
            'phone_number' => "required|unique:black_list_numbers,phone_number,$id|string|min:1|max:255",
        ]);

        $blackListNumber->phone_number = $request->phone_number;
        $blackListNumber->save();

        return $this->responseSuccess($blackListNumber, 'Item Updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlackListNumber $blackListNumber)
    {
        $blackListNumber->delete();
        return redirect()->route('black-list-numbers.index')->with('success', 'Item deleted successfully.');
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        $blackListNumber = BlackListNumber::findOrFail($id);
        $blackListNumber->delete();

        return $this->responseSuccess([], 'Item deleted successfully.');
    }

    /**
     * @param Request $request
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function getBlackListNumberForUser(Request $request)
    {
        $filter = [
            'count' => request('count', 5),
        ];
        $user_id = auth()->id();
        $list = $this->contactRepository->getBlockedListUserContacts($user_id, $filter['count']);
        return view('black_list_number.black_list_user', compact('list', 'filter'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getBlackListNumberForUserApi(Request $request)
    {
        $filter = [
            'count' => request('count', 5),
        ];
        $user_id = auth()->id();
        $list = $this->contactRepository->getBlockedListUserContacts($user_id, $filter['count']);

        return $this->responseSuccess([
            'list' => $list,
            'filter' => $filter,
        ]);
    }
}
