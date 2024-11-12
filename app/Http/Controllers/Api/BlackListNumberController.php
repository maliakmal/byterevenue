<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use App\Trait\CSVReader;
use Illuminate\Http\Request;

class BlackListNumberController extends Controller
{
    use CSVReader;
    /**
     * @param BlackListNumberRepositoryInterface $blackListNumberRepository
     */
    public function __construct(
        protected BlackListNumberRepositoryInterface $blackListNumberRepository
    )
    {
    }

    /**
     * @OA\Post(
     *     path="/blacklist-numbers/upload",
     *     summary="Update black list numbers from CSV file",
     *     tags={"Black List Numbers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="black_list_file",
     *                     type="string",
     *                     format="binary",
     *                     description="CSV file containing black list numbers"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Black list numbers updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="internal server error")
     *         )
     *     )
     * )
     * @param Request $request
     * @return mixed
     */
    public function updateBlackListNumber(Request $request)
    {
        $max_allowed_csv_upload_file = config('app.csv.upload_max_size_allowed');
        $request->validate([
            'black_list_file' => "required|max:$max_allowed_csv_upload_file",
        ], $request->all());
        $file = $request->file('black_list_file');
        $content = file_get_contents($file->getRealPath());
        $csv = $this->csvToCollection($content);
        if(!$csv || count($csv) == 0){
            response()->error('error parse csv');
        }
        if(isset($csv->first()['phone_number']) == false){
            return response()->error('column phone number not found');
        }
        $this->blackListNumberRepository->upsertPhoneNumber($csv->toArray());
        return response()->success();
    }
}
