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
        return view('accounts.show', compact('account'));
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
