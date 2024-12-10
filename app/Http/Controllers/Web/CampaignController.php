<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignStoreRequest;
use App\Http\Requests\CampaignUpdateRequest;
use App\Models\Campaign;
use App\Models\RecipientsList;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    private $campaignService;

    /**
     * @param CampaignService $campaignService
     */
    public function __construct(CampaignService $campaignService) {
        $this->campaignService = $campaignService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'sort_by' => $request->get('sort_by', 'id'),
            'sort_order' => $request->get('sort_order', 'desc'),
            'per_page' => $request->get('per_page', 5),
            'page' => $request->get('page', 1),
        ];

        $campaigns = $this->campaignService->getCampaignsFiltered($filter);

        return view('campaigns.index', compact('campaigns', 'filter'));
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
        $recipientsList = RecipientsList::find(intval($request->recipients_list_id));

        if (!$recipientsList) {
            return redirect()->route('campaigns.index')->withErrors(['error' => 'Recipient list not found.']);
        }

        $count_of_contacts = $recipientsList->recipientsGroup->count;

        if ($count_of_contacts == 0) {
            return redirect()->route('campaigns.index')->withErrors(['error' => 'Recipient list is empty.']);
        }

        $user = auth()->user();

        if (!$user->hasEnoughTokens($count_of_contacts)) {
            return redirect()->route('campaigns.index')->withErrors(['error' => 'You do not have enough tokens to send this campaign.']);
        }

        $user->deductTokens($count_of_contacts);

        [$campaign, $errors] = $this->campaignService->store($request->validated());

        if (isset($errors['message'])) {
            redirect()->route('campaigns.index')->with('error', $errors['message']);
        }

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign created successfully.');
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
     * @param int $id
     *
     * @return JsonResponse
     */
    public function campaignStats(int $id)
    {
        $campaignStats = $this->campaignService->getCampaignStats($id);

        return $this->responseSuccess($campaignStats);
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
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
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
}
