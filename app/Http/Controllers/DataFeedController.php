<?php

namespace App\Http\Controllers;

use App\Services\DataFeed\DataFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataFeedController extends Controller
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getDataFeedApi(Request $request)
    {
        return response()->json(
            $this->dataFeedService->getDataFeed(
                $request->get('dataType'),
                $request->get('limit')
            )
        );
    }
}
