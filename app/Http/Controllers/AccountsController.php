<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Transaction;
use App\Services\Accounts\AccountsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    private AccountsService $accountsService;
    /**
     * @param AccountsService $accountsService
     */
    public function __construct(AccountsService $accountsService)
    {
        $this->accountsService = $accountsService;
    }

    /**
     * @return JsonResponse
     */
    public function indexApi()
    {
        $response = $this->accountsService->getAccountsWithFilter();
        return response()->json($response);
    }

    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function showApi($id)
    {
        $response = $this->accountsService->getAccountTransactions($id);

        return response()->json($response);
    }

    /**
     * @return JsonResponse
     */
    public function showTokensApi()
    {
        $isCurrentUserAdmin = auth()->user()->hasRole('admin');
        $userId = $isCurrentUserAdmin ? null : auth()->id();
        $response = $this->accountsService->getAccountTransactions($userId);

        return response()->json($response);
    }

    /**
     * @return JsonResponse
     */
    public function storeTokensApi(Request $request)
    {
        $response = $this->accountsService->addTokensToAccount($request);
        if (isset($response['errors'])) {
            return response()->json($response, 400);
        }
        return response()->json($response);
    }

    public function index()
    {
        $filter = [
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 5),
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

        if (!empty($filter['sortby'])) {
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
        }
        $accounts = $accounts->paginate($filter['count']);

        return view('accounts.index', compact('accounts', 'filter'));
    }

    public function show($id)
    {
        $account = User::find($id);
        $transactions = Transaction::query();
        $filter = [
            'type' => request('type'),
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 5),
        ];
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
        $transactions = $transactions->get();

        return view('accounts.show', compact('account', 'filter', 'transactions'));
    }

    public function tokens()
    {

        $account = User::find(auth()->user()->id);
        $transactions = Transaction::query();

        $filter = [
            'type' => request('type'),
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 5),
        ];
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
        $transactions = $transactions->get();

        return view('accounts.tokens', compact('account', 'filter', 'transactions'));
    }

    public function storeTokens(Request $request)
    {
        $account = User::find($request->user_id);
        $amount = $request->amount;
        Transaction::create([
            'user_id' => $account->id,
            'amount' => $amount,
            'type' => 'purchase',
        ]);
        $account->addTokens($amount);
        $account->save();
        return redirect()->route('accounts.show', $account->id)->with('success', 'Tokens updated successfully.');
    }

}
