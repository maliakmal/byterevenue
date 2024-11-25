<?php

namespace App\Helpers;

class MemoryUsageHelper
{
    public static function measureMemoryUsage(callable $callback): array
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        $formatMemory = function ($bytes) use ($unit) {
            $i = floor(log($bytes, 1024));
            return round($bytes / pow(1024, $i), 2) . ' ' . $unit[$i];
        };

        $memoryBefore = memory_get_usage();
        $start = microtime(true);

        $callback();

        $time = microtime(true) - $start;
        $memoryAfter = memory_get_usage();
        $memoryPeak = memory_get_peak_usage();

        return [
            'memory_before' => $formatMemory($memoryBefore),
            'memory_peak' => $formatMemory($memoryPeak),
            'memory_after' => $formatMemory($memoryAfter),
            'time' => sprintf('%.6f sec.',$time),
        ];
    }
}
