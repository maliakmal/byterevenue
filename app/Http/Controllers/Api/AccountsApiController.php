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
     * @OA\Get(
     *     path="/accounts",
     *     summary="Get all accounts",
     *     tags={"Accounts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @return JsonResponse
     */
    public function index()
    {
        $response = $this->accountsService->getAccounts();
        return $this->responseSuccess($response);
    }

    /**
     * @OA\Get(
     *     path="/accounts/{id}",
     *     summary="Get account transactions",
     *     tags={"Accounts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Account ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @param string $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $response = $this->accountsService->getAccountTransactions($id);

        return $this->responseSuccess($response);
    }

    /**
     * @OA\Get(
     *     path="/tokens",
     *     summary="Get tokens for the current user",
     *     tags={"Accounts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @return JsonResponse
     */
    public function showTokens()
    {
        $isCurrentUserAdmin = auth()->user()->hasRole('admin');
        $userId = $isCurrentUserAdmin ? null : auth()->id();
        $response = $this->accountsService->getAccountTransactions($userId);

        return $this->responseSuccess($response);
    }

    /**
     * @return JsonResponse
     */
    public function storeTokens(Request $request)
    {
        $response = $this->accountsService->addTokensToAccount($request);
        if (isset($response['errors'])) {
            return $this->responseError($response['errors']);
        }
        return $this->responseSuccess($response);
    }
}
