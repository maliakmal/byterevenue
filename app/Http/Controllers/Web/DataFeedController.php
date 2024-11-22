<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DataFeed\DataFeedService;
use Illuminate\Http\Request;

class DataFeedController extends Controller
{
    public function __construct(
        private DataFeedService $dataFeedService,
    ) {}

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
}
