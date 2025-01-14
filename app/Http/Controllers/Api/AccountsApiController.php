<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
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
     * @param int $id
     * @return JsonResponse
     */
    public function showTokens(int $id): JsonResponse
    {
        $response = $this->accountsService->getAccountTransactions($id);

        return $this->responseSuccess($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeTokens(Request $request): JsonResponse
    {
        $response = $this->accountsService->addTokensToAccount($request);

        if (isset($response['errors'])) {
            return $this->responseError($response['errors']);
        }

        return $this->responseSuccess($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $response = $this->accountsService->delete($id);

        if (isset($response['errors'])) {
            return $this->responseError($response['errors']);
        }

        return $this->responseSuccess($response);
    }
}
