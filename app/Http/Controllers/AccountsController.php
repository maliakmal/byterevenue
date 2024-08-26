<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use App\Models\Campaign;
use App\Models\Transaction;

class AccountsController extends Controller
{
    public function index()
    {
        $filter = array(
            'sortby'=> request('sortby')?request('sortby'):'id_desc',
            'count'=> request('count')?request('count'):5,
        );
        $accounts = User::query()->with('latestCampaign')->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('campaigns')
                    ->whereColumn('campaigns.user_id', 'users.id');
            }, 'campaign_count')->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('campaigns')
                    ->whereColumn('campaigns.user_id', 'users.id')
                    ->where('status', [Campaign::STATUS_PROCESSING]);
            }, 'processing_campaign_count');

        if(!empty($filter['sortby'])){
            switch($filter['sortby']){
                case 'id_desc':
                    $accounts->orderby('id', 'desc');
                    break;
                case 'id_asc':
                    $accounts->orderby('id', 'asc');
                    break;
                case 'name':
                    $accounts->orderby('name', 'asc');
                    break;
                case 'tokens_desc':
                    $accounts->orderby('tokens', 'desc');
                    break;
                case 'tokens_asc':
                    $accounts->orderby('tokens', 'asc');
                    break;
                case 'campaigns_desc':
                    $accounts->orderby('campaign_count', 'desc');
                    break;
                case 'campaigns_asc':
                    $accounts->orderby('campaign_count', 'asc');
                    break;
            }
        }
        $accounts = $accounts->paginate($filter['count']);


        return view('accounts.index', compact('accounts', 'filter'));
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
