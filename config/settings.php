<?php

return [
    'storage' => [
        'archive_logs' => [
            // recommendation use values: everyFiveMinutes, everyTenMinutes, everyThirtyMinutes, hourly, daily
            'period' => 'everyTenMinutes',
            // max count is <= 65535 / (count of rows in a single insert)
            'count'  => 10000,
            'not_clicked_period' => 3, // days
            'not_send_period'    => 5, // days
        ],
    ],
];
