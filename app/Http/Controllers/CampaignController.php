<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CampaignStoreRequest;
use App\Http\Requests\CampaignUpdateRequest;
use App\Models\RecipientsList;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Campaign;

class CampaignController extends ApiController
{
    private $campaignService;

    /**
     * @param CampaignService $campaignService
     */
    public function __construct(
        CampaignService $campaignService,
    ) {
        $this->campaignService = $campaignService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filter = [
            'status' => request('status'),
            'user_id' => request('user_id'),
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 5),
        ];

        $campaigns = $this->campaignService->getCampaignsFiltered($filter);

        return view('campaigns.index', compact('campaigns', 'filter'));
    }

    /**
     * @OA\Get(
     *     path="/campaigns",
     *     summary="Get a list of campaigns",
     *     tags={"Campaigns"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by campaign status"
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Filter by user ID"
     *     ),
     *     @OA\Parameter(
     *         name="sortby",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Sort by field"
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Number of items per page"
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search term"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function indexApi(Request $request)
    {
        $filter = [
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'sortby' => $request->get('sortby', 'id_desc'),
            'count' => $request->get('count', 5),
            'search' => $request->get('search'),
        ];
        $campaigns = $this->campaignService->getCampaignsFiltered($filter);

        return $this->responseSuccess($campaigns);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $recipient_lists = auth()->user()->recipientLists()->get();
        return view('campaigns.create', compact('recipient_lists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampaignStoreRequest $request)
    {
        [$campaign, $errors] = $this->campaignService->store($request->validated());

        if (isset($errors['message'])) {
            redirect()->route('campaigns.index')->with('error', $errors['message']);
        }

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign created successfully.');
    }

    /**
     * @OA\Post(
     *     path="/campaigns",
     *     summary="Store a new campaign",
     *     tags={"Campaigns"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Campaign Name"),
     *             @OA\Property(property="description", type="string", example="Campaign Description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaign created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     )
     * )
     * @param CampaignStoreRequest $request
     *
     * @return JsonResponse
     */
    public function storeApi(CampaignStoreRequest $request)
    {
        [$campaign, $errors] = $this->campaignService->store($request->validated());

        if (isset($errors['message'])) {
            return $this->responseError([], $errors);
        }

        return $this->responseSuccess($campaign, 'Campaign created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $campaignData = $this->campaignService->show($campaign->id, []);

        if (request()->input('output') == 'json') {
            return response()->success(null, [
                'contacts' => $campaignData['contacts'],
                'logs' => $campaignData['logs'],
            ]);
        }

        return view('campaigns.show')->with($campaignData);
    }

    /**
     * @OA\Get(
     *     path="/campaigns/{id}",
     *     summary="Get a campaign",
     *     tags={"Campaigns"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Campaign ID"
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Number of items per page"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Page number"
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Sort by field"
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Sort order (asc or desc)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="campaign", type="object"),
     *             @OA\Property(property="message", type="object"),
     *             @OA\Property(property="contacts", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="logs", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showApi(int $id, Request $request)
    {
        $filters = [
            'per_page' => $request->get('per_page', 5),
            'page' => $request->get('page', 1),
            'sort_by' => $request->get('sort_by', 'id_desc'),
            'sort_order' => $request->get('sort_order', 'asc'),
        ];
        $campaignData = $this->campaignService->show($id, $filters);

        return $this->responseSuccess($campaignData);
    }

    /**
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function markAsProcessed(int $id)
    {
        [$result, $message] = $this->campaignService->markAsProcessed($id);

        if ($result) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->withErrors(['error' => $message]);
    }

    /**
     * @OA\Post(
     *     path="/campaigns/mark-processed/{id}",
     *     summary="Mark a campaign as processed",
     *     tags={"Campaigns"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Campaign ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaign marked as processed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Campaign marked as processed.")
     *         )
     *     )
     * )
     * @param int $id
     *
     * @return JsonResponse
     */
    public function markAsProcessedApi(int $id)
    {
        [$result, $message] = $this->campaignService->markAsProcessed($id);

        if ($result) {
            return $this->responseSuccess([], $message);
        }

        return $this->responseError([], $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        $recipient_lists = RecipientsList::where('user_id', $campaign->user_id)->get();
        return view('campaigns.edit', compact('campaign', 'recipient_lists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CampaignUpdateRequest $request, Campaign $campaign)
    {
        $campaign = $this->campaignService->update($campaign->id, $request->validated());
        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign updated successfully.');
    }

    /**
     * @OA\Put(
     *     path="/campaigns/{id}",
     *     summary="Update a campaign",
     *     tags={"Campaigns"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Campaign ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Campaign Name"),
     *             @OA\Property(property="description", type="string", example="Updated Campaign Description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaign updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     )
     * )
     * @param int $id
     * @param CampaignUpdateRequest $request
     *
     * @return JsonResponse
     */
    public function updateApi(int $id, CampaignUpdateRequest $request)
    {
        $campaign = $this->campaignService->update($id, $request->validated());

        return $this->responseSuccess($campaign, 'Campaign updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/campaigns/{id}",
     *     summary="Delete a campaign",
     *     tags={"Campaigns"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Campaign ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaign deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Campaign deleted successfully.")
     *         )
     *     )
     * )
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        Campaign::find($id)->delete();

        return $this->responseSuccess([], 'Campaign deleted successfully.');
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getCampaignForUser(Request $request)
    {
        $request->validate(['user_id' => 'required|numeric']);
        $user_id = $request->user_id;
        $campaigns = $this->campaignService->getCampaignsForUser($user_id);

        return response()->success(null, $campaigns);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCampaignForUserApi(Request $request)
    {
        $request->validate(['user_id' => 'required|numeric']);
        $user_id = $request->user_id;
        $campaigns = $this->campaignService->getCampaignsForUser($user_id);

        return $this->responseSuccess($campaigns);
    }
}
