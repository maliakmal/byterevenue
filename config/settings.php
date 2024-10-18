<?php

return [
    'storage' => [
        'archive_logs' => [
            // recommendation use values: everyFiveMinutes, everyTenMinutes, everyThirtyMinutes, hourly, daily
            'period' => 'hourly',
            // max count is <= 65535 / (count of rows in a single insert)
            'count'  => 10000,
            'not_clicked_period' => 1, // days
            'total_period'       => 7, // days
        ],
    ],
];
