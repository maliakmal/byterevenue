<?php

namespace App\Services\Accounts;

use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use function PHPUnit\Framework\isNull;


class AccountsService
{
    /**
     * @return LengthAwarePaginator
     */
    public function getAccounts(Request $request)
    {
        $filter = [
            'account' => request('account'),
            'sort_by' => request('sort_by', 'id'),
            'sort_order' => request('sort_order', 'desc'),
            'per_page' => request('per_page', 15),
        ];

        $status = intval($request->input('status',-1));

        $accounts = User::withCount([
            'campaigns',
            'campaigns as processing_campaign_count' => function ($query) {
                $query->where('status', Campaign::STATUS_PROCESSING);
            },
            'recipientLists'
        ])
            ->addSelect([
                'latest_campaign_total_ctr' => Campaign::select('total_ctr')
                    ->whereColumn('user_id', 'users.id')
                    ->latest('id')
                    ->limit(1),
                'latest_transaction_date' => Transaction::select('created_at')
                    ->whereColumn('user_id', 'users.id')
                    ->latest('created_at')
                    ->limit(1),
                'sent' => Campaign::selectRaw('SUM(total_recipients_sent_to)')
                    ->whereColumn('user_id', 'users.id'),
                'clicked' => Campaign::selectRaw('SUM(total_recipients_click_thru)')
                    ->whereColumn('user_id', 'users.id'),
                'campaigns_average_ctr' => Campaign::selectRaw('AVG(total_ctr)')
                    ->whereColumn('user_id', 'users.id'),
            ])
            ->when(in_array($status, [0, 1]), function ($query) use ($status) {
                return $query->where('is_blocked', $status);
            });

        if (!empty($filter['account'])) {
            $accounts->where('name', 'like', '%' . $filter['account'] . '%')->orWhere('email', 'like', '%' . $filter['account'] . '%');
        }

        $accounts = $accounts->orderBy($filter['sort_by'], $filter['sort_order'])->paginate($filter['per_page']);

        return $accounts;
    }

    /**
     * @param string|null $id
     *
     * @return mixed
     */
    public function getAccountTransactions($id)
    {
        $id = auth()->user()->hasRole('admin') ? $id : auth()->id();

        $transactions = Transaction::where('user_id', $id);

        $filter = [
            'sort_by' => request('sort_by', 'id'),
            'sort_order' => request('sort_order', 'desc'),
            'type' => request('type'),
            'per_page' => request('per_page', 15),
        ];

        if (!empty($filter['type'])) {
            $transactions->where('type', $filter['type']);
        }

        $transactions = $transactions->orderBy($filter['sort_by'], $filter['sort_order'])->paginate($filter['per_page']);
        $user = User::select(['name', 'created_at'])->find($id);
        return [
            'transactions' => $transactions,
            'user' => $user
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function cahngeTokensInAccount(User $user, int $amount)
    {
        if ($amount < 0) {
            $user->deductTokens(abs($amount));
        } elseif ($amount > 0) {
            $user->addTokens(abs($amount));
        }

        return ['message' => 'Tokens updated successfully.'];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function hiddenCahngeTokensInAccount(User $user, int $amount)
    {
        if ($amount < 0) {
            $user->hiddenDeductTokens(abs($amount));
        } elseif ($amount > 0) {
            $user->hiddenAddTokens(abs($amount));
        }

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

    public function delete($id)
    {
        User::whereId($id)->delete();

        return ['message' => 'Account deleted successfully.'];
    }
}
