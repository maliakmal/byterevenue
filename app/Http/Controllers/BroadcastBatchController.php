<?php

namespace App\Http\Controllers;

use App\Models\BroadcastBatch;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\RecipientsList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $recipient_lists = RecipientsList::where('user_id', auth()->id())->get();
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
            'subject' => $request->message_subject,
            'body' => $request->message_body,
            'target_url' => $request->message_target_url,
            "user_id" => auth()->id(),
            'campaign_id' => $campaign_id,
        ];

        $message = Message::create($message_data);

        $broadcast_batch_data = [
            'recipients_list_id' => $request->recipients_list_id,
            'user_id' => auth()->id(),
            'campaign_id' => $campaign_id,
            'message_id' => $message->id,
            'status' => 0,
        ];

        BroadcastBatch::create($broadcast_batch_data);
        return redirect()->route('campaigns.show', $campaign)->with('success', 'Broadcast Job created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(BroadcastBatch $broadcastBatch)
    {
        $campaign = $broadcastBatch->campaign;
        $message = $broadcastBatch->message;

        $recipient_lists = $broadcastBatch->recipient_list;
        $broadcast_batch = $broadcastBatch;

        if ($broadcast_batch->isDraft()) {
            $contacts = $recipient_lists->contacts()->paginate(10);
            $logs = [];

        } else {
            $contacts = [];
            $logs = BroadcastLog::select()->where('broadcast_batch_id', '=', $broadcast_batch->id)->paginate(10);
        }
        return view('broadcast_batch.show', compact('campaign', 'contacts', 'logs', 'broadcast_batch', 'message', 'recipient_lists'));

    }

    public function markAsProcessed($id)
    {
        // create message logs against each contact and generate the message acordingly

        DB::beginTransaction();

        try {
            $broadcast_batch = BroadcastBatch::findOrFail($id);

            $message = $broadcast_batch->message->getParsedMessage();
            $data = [
                'user_id' => auth()->id(),
                'recipients_list_id' => $broadcast_batch->recipient_list->id,
                'message_id' => $broadcast_batch->message_id,
                'message_body' => $message,
                'recipient_phone' => '',
                'contact_id' => 0,
                'is_downloaded_as_csv' => 0,
                'broadcast_batch_id' => $broadcast_batch->id
            ];

            $contacts = $broadcast_batch->recipient_list->contacts->all();

            foreach ($contacts as $contact) {
                $data['recipient_phone'] = $contact->phone;
                $data['contact_id'] = $contact->id;
                BroadcastLog::create($data);
            }

            $broadcast_batch->markAsProcessed();
            $broadcast_batch->save();
            DB::commit();
            return redirect()->back()->with('success', 'Job is being processed.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'An error occurred - please try again later.']);
        }

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
