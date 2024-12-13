<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BroadcastBatchStoreRequest;
use App\Models\BroadcastBatch;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\RecipientsList;
use App\Services\BroadcastBatch\BroadcastBatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BroadcastBatchController extends Controller
{
    public function __construct(
        private BroadcastBatchService $broadcastBatchService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $campaign_id = $request->query('campaign_id');
        $campaign = Campaign::find($campaign_id);
        $recipient_lists = RecipientsList::where('user_id', auth()->id())->get();

        return view('broadcast_batch.create', compact('campaign', 'recipient_lists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BroadcastBatchStoreRequest $request)
    {
        [$campaign, $broadcast_batch] = $this->broadcastBatchService->store($request->validated());

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Broadcast Job created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BroadcastBatch $broadcastBatch)
    {
        $recipient_lists = $broadcastBatch->recipient_list;

        if ($broadcastBatch->isDraft()) {
            $contacts = $recipient_lists->recipientsGroup->getAllContactsPaginated(10);
            $logs = [];

        } else {
            $contacts = [];
            $logs = BroadcastLog::select()
                ->where('recipients_list_id', '=', $broadcastBatch->recipients_list_id)
                ->paginate(10);
        }

        return view('broadcast_batch.show')->with(
            [
                'campaign' => $broadcastBatch->campaign,
                'contacts' => $contacts,
                'logs' => $logs,
                'broadcast_batch' => $broadcastBatch,
                'message' => $broadcastBatch->message,
                'recipient_lists' => $recipient_lists,
            ]
        );
    }

    /**
     * @param $id
     *
     * @return RedirectResponse
     */
    public function markAsProcessed($id)
    {
        [$result, $message] = $this->broadcastBatchService->markedAsProcessed(intval($id));

        if ($result) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->withErrors(['error' => $message]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BroadcastBatch $broadcastBatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BroadcastBatch $broadcastBatch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BroadcastBatch $broadcastBatch)
    {
        //
    }
}
