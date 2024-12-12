<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Requests\CampaignStoreRequest;
use App\Http\Requests\CampaignUpdateRequest;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignSwaggerController extends SwaggerController
{
    /**
     * @OA\Get(
     *     path="/api/campaigns/mark-processed/{id}",
     *     summary="Mark campaign as processed",
     *     tags={"Campaigns"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Campaign ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaign marked as processed"
     *     )
     * )
     */
    public function markAsIgnoreFromQueue(Request $request) {}

    public function markAsNotIgnoreFromQueue(Request $request) {}

    /**
     * @OA\Get(
     *     path="/api/campaigns",
     *     summary="Get all campaigns",
     *     tags={"Campaigns"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of campaigns",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     )
     * )
     */
    public function index(Request $request) {}

    public function show(int $id, Request $request) {}

    /**
     * @OA\Put(
     *     path="/api/campaigns/create",
     *     summary="Create a new campaign",
     *     tags={"Campaigns"},
     *     security={{"bearerAuth": {}}},
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
     *         description="Campaign created"
     *     )
     * )
     */
    public function store(CampaignStoreRequest $request) {}

    public function update(CampaignUpdateRequest $request, Campaign $campaign) {}

    public function destroy(Campaign $campaign) {}
}
