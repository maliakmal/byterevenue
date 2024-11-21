<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlacklistNumberStoreRequest;
use App\Models\BlackListNumber;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use App\Repositories\Contract\Contact\ContactRepositoryInterface;
use App\Services\BlacklistNumber\BlacklistNumberService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlackListNumberController extends Controller
{
    public function __construct(
        protected BlackListNumberRepositoryInterface $blackListNumberRepository,
        protected ContactRepositoryInterface $contactRepository,
        protected BlacklistNumberService $blacklistNumberService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'count'=> request('count', 5),
        ];

        $list = $this->blackListNumberRepository->paginate($request->count);

        return view('black_list_number.index', compact('list', 'filter'));
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
        $request->validate([
            'phone_number' => "required|unique:black_list_numbers,phone_number,". $blackListNumber->id ."|string|min:1|max:255",
        ]);

        $blackListNumber->phone_number = $request->phone_number;
        $blackListNumber->save();

        return redirect()->route('black-list-numbers.index', $blackListNumber)->with('success', 'Item Updated successfully.');
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
}
