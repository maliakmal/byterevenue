<?php

namespace App\Http\Controllers;

use App\Models\BroadcastBatch;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\RecipientsList;
use Illuminate\Http\Request;

class BroadcastBatchController extends Controller
{

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
        //
        $campaign_id = $request->query('campaign_id');
        $campaign = Campaign::find($campaign_id);

        $recipient_lists = RecipientsList::select()->where('user_id', auth()->id())->get();
        return view('broadcast_batch.create', compact('campaign', 'recipient_lists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $campaign_id = $request->campaign_id;

        $campaign = Campaign::find($campaign_id);
        //

        $message_data = [
            'subject'=>$request->message_subject,
            'body'=>$request->message_body,
            'target_url'=>$request->message_target_url,
            "user_id"=>auth()->user()->id,
            'campaign_id'=>$campaign_id
        ];

        $message = Message::create($message_data);

        $broadcast_batch_data = [
            'recipients_list_id' => $request->recipients_list_id, 
            'user_id'=>auth()->id(),
            'campaign_id'=>$campaign->id,
            'message_id'=>$message->id, 'status'=>0];

        $broadcast_batch = BroadcastBatch::create($broadcast_batch_data);
        return redirect()->route('campaigns.show', $campaign)->with('success', 'Broadcast Job created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(BroadcastBatch $broadcastBatch)
    {
        //
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
