<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use Illuminate\Http\Request;

class _JobsApiController extends ApiController
{
    /**
     * @param BlackListNumberRepositoryInterface $blackListNumberRepository
     */
    public function __construct(
        protected BlackListNumberRepositoryInterface $blackListNumberRepository
    ) {}

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateBlackListNumber(Request $request)
    {
        $request->validate([
            'black_list_file' => "required|max:" . config('app.csv.upload_max_size_allowed'),
        ], $request->all());

        $file = $request->file('black_list_file');
        $content = file_get_contents($file->getRealPath());
        $csv = $this->csvToCollection($content);

        if (!$csv || count($csv) == 0) {
            response()->error('error parse csv');
        }

        if (isset($csv->first()['phone_number']) == false) {
            return response()->error('column phone number not found');
        }

        $this->blackListNumberRepository->upsertPhoneNumber($csv->toArray());

        // todo check on frontend
        // return response()->success();
        $this->responseSuccess();
    }
}
