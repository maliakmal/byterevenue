<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use App\Trait\CSVReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlackListNumberApiController extends ApiController
{
    use CSVReader;

    /**
     * @param BlackListNumberRepositoryInterface $blackListNumberRepository
     */
    public function __construct(
        protected BlackListNumberRepositoryInterface $blackListNumberRepository
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateBlackListNumber(Request $request): JsonResponse
    {
        $request->validate([
            'black_list_file' => "required|max:". config('app.csv.upload_max_size_allowed'),
        ], $request->all());

        $file = $request->file('black_list_file');
        $content = file_get_contents($file->getRealPath());
        $csv = $this->csvToCollection($content);

        if (!$csv || count($csv) == 0) {
            return $this->responseError(message: 'error parse csv');
        }

        if (isset($csv->first()['phone_number']) == false) {
            return $this->responseError(message: 'column phone number not found');
        }

        $this->blackListNumberRepository->upsertPhoneNumber($csv->toArray());

        return $this->responseSuccess(message: 'success');
    }
}
