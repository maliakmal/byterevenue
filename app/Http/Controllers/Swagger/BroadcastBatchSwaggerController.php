<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Requests\BroadcastBatchStoreRequest;
use Illuminate\Http\JsonResponse;

class BroadcastBatchSwaggerController extends SwaggerController
{
    /**
     * @OA\Post(
     *     path="/broadcast_batches",
     *     summary="Store a new broadcast batch",
     *     tags={"Broadcast Batches"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="campaign_id", type="integer", example=1),
     *             @OA\Property(property="recipients_list_id", type="integer", example=1),
     *             @OA\Property(property="message_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Broadcast Job created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="campaign", type="object"),
     *             @OA\Property(property="broadcast_batch", type="object")
     *         )
     *     )
     * )
     * @param BroadcastBatchStoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(BroadcastBatchStoreRequest $request) {}

    /**
     * @OA\Get(
     *     path="/broadcast_batches/{id}",
     *     summary="Get a broadcast batch",
     *     tags={"Broadcast Batches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Broadcast Batch ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="campaign", type="object"),
     *             @OA\Property(property="contacts", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="logs", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="broadcast_batch", type="object"),
     *             @OA\Property(property="message", type="object"),
     *             @OA\Property(property="recipient_lists", type="object")
     *         )
     *     )
     * )
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id) {}

    /**
     * @OA\Post(
     *     path="/broadcast_batches/mark_as_processed/{id}",
     *     summary="Mark a broadcast batch as processed",
     *     tags={"Broadcast Batches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Broadcast Batch ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Broadcast Batch marked as processed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Broadcast Batch marked as processed.")
     *         )
     *     )
     * )
     * @param int $id
     *
     * @return JsonResponse
     */
    public function markAsProcessed(int $id) {}
}
