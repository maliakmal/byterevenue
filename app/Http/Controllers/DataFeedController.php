<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Services\DataFeed\DataFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataFeedController extends ApiController
{
    private $dataFeedService;

    /**
     * @param DataFeedService $dataFeedService
     */
    public function __construct(DataFeedService $dataFeedService)
    {
        $this->dataFeedService = $dataFeedService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getDataFeed(Request $request)
    {
        return $this->dataFeedService->getDataFeed(
            $request->get('dataType'),
            $request->get('limit')
        );
    }

    /**
     * @OA\Get(
     *     path="/json-data-feed",
     *     summary="Get data feed",
     *     tags={"DataFeed"},
     *     @OA\Parameter(
     *         name="dataType",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Type of data to fetch"
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Limit the number of results"
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
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataFeedApi(Request $request)
    {
        $data = $this->dataFeedService->getDataFeed(
            $request->get('dataType'),
            $request->get('limit')
        );

        return $this->responseSuccess($data);
    }
}
