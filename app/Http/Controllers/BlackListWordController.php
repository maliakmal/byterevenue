<?php

namespace App\Http\Controllers;

use App\Models\BlackListWord;
use App\Repositories\Contract\BlackListWord\BlackListWordRepositoryInterface;
use Illuminate\Http\Request;

class BlackListWordController extends Controller
{
    public function __construct(
        protected BlackListWordRepositoryInterface $blackListWordRepository,
    )
    {
    }

    /**
         * Display a listing of the resource.
         */
        public function index(Request $request)
        {
            $filter = array(
                'count'=> request('count')?request('count'):5,
            );
            $list = $this->blackListWordRepository->paginate($request->count);
            return view('black_list_word.index', compact('list', 'filter' ));
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
        public function store(Request $request)
        {
            $request->validate([
                'word' => 'required|string|min:1',
            ]);
            $word_string = $request->word;
            $list = collect(explode("\n", $word_string));
            $list = $list->reject(function ($item){
                return empty($item);
            });
            $list = $list->map(function ($item) {
                return ['word' => trim($item)];
            });
            $black_list_word = $this->blackListWordRepository->upsertWord($list->toArray());
            return redirect()->route('black-list-words.index')->with('success', 'The Item created successfully.');
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
            $request->all();
            $id = $blackListWord->id;
            $request->validate([
                'word' => "required|unique:black_list_words,word,$id|string|min:1|max:255",
            ]);
            $blackListWord->word = $request->word;
            $blackListWord->save();
            return redirect()->route('black-list-words.index', $blackListWord)->with('success', 'Item Updated successfully.');
        }

        /**
         * Remove the specified resource from storage.
         */
        public function destroy(BlackListWord $blackListWord)
        {
            $blackListWord->delete();
            return redirect()->route('black-list-words.index')->with('success', 'Item deleted successfully.');
        }
}
