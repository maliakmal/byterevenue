<?php

namespace App\Services\Accounts;

use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;
use function PHPUnit\Framework\isNull;


class AccountsService
{
    /**
     * @return LengthAwarePaginator
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

        return $accounts;
    }

    /**
     * @param string|null $id
     *
     * @return LengthAwarePaginator
     */
    public function getAccountTransactions($id = null)
    {
        $transactions = Transaction::with(['user'])->when($id, function ($query) use ($id) {
            return $query->where('user_id', $id);
        });
        $filter = [
            'username' => request('search'),
            'sort_by' => request('sort_by', 'id'),
            'sort_order' => request('sort_order', 'desc'),
            'type' => request('type'),
            'per_page' => request('per_page', 15),
        ];

        if (!empty($filter['username'])) {
            $transactions->whereHas('user', function ($query) use ($filter) {
                $query->where('name', $filter['username']);
            });
        }
        if (!empty($filter['type'])) {
            $transactions->where('type', $filter['type']);
        }

        $transactions = $transactions->orderBy($filter['sort_by'], $filter['sort_order'])->paginate($filter['per_page']);

        return $transactions;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function addTokensToAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        $data = $validator->validated();

        $account = User::find($data['user_id']);
        $account->addTokens($data['amount']);
        return ['message' => 'Tokens updated successfully.'];
    }

    /**
     * @param int|null $userId
     * @param array $filter
     *
     * @return Collection
     */
    public function getTransactions(?int $userId, array $filter = [])
    {
        $transactions = Transaction::query();

        if (!isNull($userId)) {
            $transactions->where('user_id', $userId);
            ;
        }

        if (!empty($filter['type'])) {
            $transactions->where('type', $filter['type']);
        }

        if (!empty($filter['sortby'])) {
            switch ($filter['sortby']) {
                case 'id_desc':
                    $transactions->orderby('id', 'desc');
                    break;
                case 'id_asc':
                    $transactions->orderby('id', 'asc');
                    break;
            }
        }

        return $transactions->get();
    }
}
