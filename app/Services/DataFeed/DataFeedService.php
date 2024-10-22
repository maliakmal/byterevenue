<?php

namespace App\Services\DataFeed;

use App\Models\DataFeed;

class DataFeedService
{
    /**
     * @param string $dataType
     * @param int $limit
     *
     * @return array
     */
    public function getDataFeed($dataType, $limit)
    {
        $df = new DataFeed();

        return [
            'labels' => $df->getDataFeed(
                $dataType,
                'label',
                $limit
            ),
            'data' => $df->getDataFeed(
                $dataType,
                'data',
                $limit
            ),
        ];
    }
}
