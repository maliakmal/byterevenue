<?php

namespace App\Services;

use App\Models\BatchFile;
use App\Models\BroadcastLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BatchFileDownloadService
{
    const UPLOAD_URL = 'http://172.16.1.109:8000/vm-api/send-campaign';
//    const UPLOAD_URL = 'https://webhook.site/48e8d317-7560-407e-932c-4fc07d16e693';

    public function streamingNewBatchFile(BatchFile $batch)
    {
        $filePath = "batch_files/{$batch->id}.csv";

        if (Storage::exists($filePath)) {
            return response()->download(Storage::path($filePath));
        }

        $batch_no = $batch->getBatchFromFilename();

        Storage::put($filePath, '');

        $handle = fopen(Storage::path($filePath), 'w');

        if (!$handle) {
            return [
                'error' => 'Unable to create file',
                'code'  => 500
            ];
        }

        fputcsv($handle, ['UID', 'Phone', 'Subject', 'Text']);

        $rows = BroadcastLog::where('batch', $batch_no)->cursor();

        foreach ($rows as $row) {
            fputcsv($handle, [
                trim($row->slug),
                trim($row->recipient_phone),
                '',
                trim($row->message_body),
            ]);
        }

        fclose($handle);

        return response()->streamDownload(function () use ($filePath) {
            readfile(Storage::path($filePath));
        }, "{$batch->id}.csv", [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $batch->id . '.csv"',
        ]);
    }

    public function uploadFileToResource($batch)
    {
        $filePath = "batch_files/{$batch->id}.csv";

        if (!Storage::exists($filePath)) {
            $batch_no = $batch->getBatchFromFilename();

            Storage::put($filePath, '');

            $handle = fopen(Storage::path($filePath), 'w');

            if (!$handle) {
                return [
                    'error' => 'Unable to create file',
                    'code'  => 500
                ];
            }

            fputcsv($handle, ['UID', 'Phone', 'Subject', 'Text']);

            $rows = BroadcastLog::where('batch', $batch_no)->cursor();

            foreach ($rows as $row) {
                fputcsv($handle, [
                    trim($row->slug),
                    trim($row->recipient_phone),
                    '',
                    trim($row->message_body),
                ]);
            }

            fclose($handle);
        }

        $file = Storage::get($filePath);

        try {
            Http::acceptJson()
                ->attach('campaign_file', $file, "{$batch->id}.csv")
                ->post(self::UPLOAD_URL);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return [
                'error' => 'Unable to upload file',
                'code'  => 500
            ];
        }

        return ['success' => 'File uploaded successfully'];
    }
}
