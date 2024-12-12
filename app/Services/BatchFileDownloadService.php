<?php

namespace App\Services;

use App\Models\BatchFile;
use App\Models\BroadcastLog;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BatchFileDownloadService
{
    public function streamingNewBatchFile($filename): StreamedResponse
    {
        $batch = BatchFile::find($filename);

        return new StreamedResponse(function () use ($batch) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['UID', 'Phone', 'Subject', 'Text']);

            $batch_no = $batch->getBatchFromFilename();

            $rows = BroadcastLog::select()->where('batch', '=', $batch_no)->cursor();

            foreach ($rows as $row) {
                fputcsv($handle, [
                    trim($row->slug),
                    trim($row->recipient_phone),
                    '',
                    trim($row->message_body),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }
}
