<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Services\Accounts\AccountsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountsApiController extends ApiController
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
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'account'    => 'sometimes|nullable|string',
            'sort_by'    => 'sometimes|nullable|string',
            'sort_order' => 'sometimes|nullable|in:asc,desc',
            'per_page'   => 'sometimes|nullable|integer',
            'status'     => 'sometimes|nullable|integer',
        ]);

        $response = $this->accountsService->getAccounts($request);

        return $this->responseSuccess($response);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $response = $this->accountsService->getAccountTransactions($id);

        return $this->responseSuccess($response);
    }

    /**
     * Customer method
     * @param int $id
     * @return JsonResponse
     */
    public function showTokens(int $id): JsonResponse
    {
        if (!auth()->user()->isAdmin() && auth()->id() !== $id) {
            return $this->responseError(message: 'You do not have permission to view this account');
        }

        $response = $this->accountsService->getAccountTransactions($id);

        return $this->responseSuccess($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeTokens(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|integer',
            'amount'   => 'required|integer',
            'hidden'   => 'sometimes|nullable|boolean',
            'is_usage' => 'sometimes|nullable|boolean',
        ]);

        $account = User::find(intval($request->user_id));

        if (!$account) {
            return $this->responseError(message: 'Account not found');
        }

        if ($request->hidden) {
            $response = $this->accountsService->hiddenCahngeTokensInAccount($account, intval($request->amount));
        } else {
            $response = $request->is_usage ?
                $this->accountsService->cahngeTokensInAccount($account, intval($request->amount)) :
                $this->accountsService->usageTokensFromAccount($account, intval($request->amount));
        }

        return $this->responseSuccess($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        try {
            $response = $this->accountsService->delete($id);
        } catch (\Exception $exception) {
            return $this->responseError(message: 'This account has some actions and cannot be deleted');
        }

        return $this->responseSuccess($response);
    }
}
