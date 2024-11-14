<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\BlacklistWordStoreRequest;
use App\Models\BlackListWord;
use App\Services\BlacklistWord\BlacklistWordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlackListWordController extends ApiController
{
    public function __construct(
        private BlacklistWordService $blacklistWordService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'count'=> request('count', 5),
        ];
        $list = $this->blacklistWordService->list($filter['count']);
        return view('black_list_word.index', compact('list', 'filter' ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexApi(Request $request)
    {
        $filter = [
            'count'=> request('count', 5),
        ];
        $list = $this->blacklistWordService->list($filter['count']);

        return $this->responseSuccess(
            [
                'list' =>$list,
                'filter' => $filter,
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('black_list_word.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlacklistWordStoreRequest $request)
    {
        $request->validate([
            'word' => 'required|string|min:1',
        ]);

        $this->blacklistWordService->store($request->validated());

        return redirect()->route('black-list-words.index')->with('success', 'The Item created successfully.');
    }

    /**
     * @param BlacklistWordStoreRequest $request
     *
     * @return JsonResponse
     */
    public function storeApi(BlacklistWordStoreRequest $request)
    {
        $blacklist = $this->blacklistWordService->store($request->validated());

        return $this->responseSuccess($blacklist);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlackListWord $blackListWord)
    {
        return view('black_list_word.edit', compact('blackListWord'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlackListWord $blackListWord)
    {
        $id = $blackListWord->id;
        $request->validate([
            'word' => "required|unique:black_list_words,word,$id|string|min:1|max:255",
        ]);
        $blackListWord->word = $request->word;
        $blackListWord->save();
        return redirect()->route('black-list-words.index', $blackListWord)->with('success', 'Item Updated successfully.');
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateApi(int $id, Request $request)
    {
        $blackListWord = BlackListWord::findOrFail($id);
        $request->validate([
            'word' => "required|unique:black_list_words,word,$id|string|min:1|max:255",
        ]);
        $blackListWord->word = $request->word;
        $blackListWord->save();

        return $this->responseSuccess($blackListWord);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlackListWord $blackListWord)
    {
        $blackListWord->delete();
        return redirect()->route('black-list-words.index')->with('success', 'Item deleted successfully.');
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        $blackListWord = BlackListWord::findOrFail($id);
        $blackListWord->delete();

        return $this->responseSuccess($blackListWord);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $blackListWord = BlackListWord::findOrFail($id);

        return $this->responseSuccess($blackListWord);
    }
}
