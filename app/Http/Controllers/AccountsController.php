<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use App\Models\Transaction;

class AccountsController extends Controller
{
    public function index()
    {
        $accounts = User::select()->orderby('name', 'asc')->paginate(5);

        return view('accounts.index', compact('accounts'));
    }

    public function show($id){
        $account = User::find($id);
        $transactions = Transaction::query();
        $filter = array(
            'type'=> request('type')?request('type'):null,
            'sortby'=> request('sortby')?request('sortby'):'id_desc',
            'count'=> request('count')?request('count'):5,
        );
            if(!empty($filter['type'])){
                $transactions->where('type', $filter['type']);
            }

            if(!empty($filter['sortby'])){
                switch($filter['sortby']){
                    case 'id_desc':
                        $transactions->orderby('id', 'desc');
                        break;
                    case 'id_asc':
                        $transactions->orderby('id', 'asc');
                        break;
                }
            }
            $transactions = $transactions->get()->all();

        return view('accounts.show', compact('account','filter','transactions'));
    }

    public function tokens(){

        $account = User::find(auth()->user()->id);
        $transactions = Transaction::query();

        $filter = array(
            'type'=> request('type')?request('type'):null,
            'sortby'=> request('sortby')?request('sortby'):'id_desc',
            'count'=> request('count')?request('count'):5,
        );
            if(!empty($filter['type'])){
                $transactions->where('type', $filter['type']);
            }

            if(!empty($filter['sortby'])){
                switch($filter['sortby']){
                    case 'id_desc':
                        $transactions->orderby('id', 'desc');
                        break;
                    case 'id_asc':
                        $transactions->orderby('id', 'asc');
                        break;
                }
            }
            $transactions = $transactions->get()->all();

        return view('accounts.tokens', compact('account','filter','transactions'));
    }

    public function storeTokens(Request $request){
        $account = User::find($request->user_id);
        $amount = $request->amount;
        Transaction::create([
            'user_id'=>$account->id,
            'amount'=>$amount,
            'type'=>'purchase',
        ]);
        $account->addTokens($amount);
        $account->save();
        return redirect()->route('accounts.show', $account->id)->with('success', 'Tokens updated successfully.');
    }

}
