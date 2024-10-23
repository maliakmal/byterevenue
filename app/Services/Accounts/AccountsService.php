<?php

namespace App\Services\Accounts;

use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;


class AccountsService
{
    /**
     * @return array
     */
    public function getAccounts()
    {
        $filter = [
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 15),
        ];
        $accounts = User::withCount([
            'campaigns',
            'campaigns as processing_campaign_count' => function ($query) {
                $query->where('status', Campaign::STATUS_PROCESSING);
            }
        ])
            ->addSelect([
                'latest_campaign_total_ctr' => Campaign::select('total_ctr')
                    ->whereColumn('user_id', 'users.id')
                    ->latest('id')
                    ->limit(1)
            ]);

        switch ($filter['sortby']) {
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

        $accounts = $accounts->paginate($filter['count']);

        return compact('accounts');
    }

    /**
     * @param string|null $id
     *
     * @return array
     */
    public function getAccountTransactions($id = null)
    {
        $transactions = Transaction::when($id, function ($query) use ($id) {
            return $query->where('user_id', $id);
        });
        $filter = [
            'type' => request('type'),
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 15),
        ];
        if (!empty($filter['type'])) {
            $transactions->where('type', $filter['type']);
        }

        switch ($filter['sortby']) {
            case 'id_desc':
                $transactions->orderby('id', 'desc');
                break;
            case 'id_asc':
                $transactions->orderby('id', 'asc');
                break;
        }

        $transactions = $transactions->paginate($filter['count']);

        return compact('transactions');
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function addTokensToAccount(Request $request)
    {
        $validator = Validator  ::make($request->all(), [
            'user_id' => ['required','exists:users,id'],
            'amount' => ['required','numeric'],
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        $data = $validator->validated();

        $account = User::find($data['user_id']);
        $account->addTokens($data['amount']);
        return ['message' => 'Tokens updated successfully.'];
    }
}
