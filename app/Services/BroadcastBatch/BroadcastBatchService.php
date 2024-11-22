<?php

namespace App\Services\BroadcastBatch;

use App\Models\BroadcastBatch;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class BroadcastBatchService
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data)
    {
        $campaign_id = $data['campaign_id'];

        $campaign = Campaign::find($campaign_id);

        $message_data = [
            'subject' => $data['message_subject'],
            'body' => $data['message_body'],
            'target_url' => $data['message_target_url'],
            "user_id" => auth()->id(),
            'campaign_id' => $campaign_id,
        ];

        $message = Message::create($message_data);

        $broadcast_batch_data = [
            'recipients_list_id' => $data['recipients_list_id'],
            'user_id' => auth()->id(),
            'campaign_id' => $campaign_id,
            'message_id' => $message->id,
            'status' => 0,
        ];

        $batch = BroadcastBatch::create($broadcast_batch_data);

        return [$campaign, $batch];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function markedAsProcessed(int $id)
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
                'campaign_id' => $broadcast_batch->campaign_id,
                // 'broadcast_batch_id' => $broadcast_batch->id TODO: ask about this
            ];

            $contacts = $broadcast_batch->recipient_list->contacts;

            foreach ($contacts as $contact) {
                $data['recipient_phone'] = $contact->phone;
                $data['contact_id'] = $contact->id;
                BroadcastLog::create($data);
            }

            $broadcast_batch->markAsProcessed();
            $broadcast_batch->save();
            DB::commit();

            return [true, 'Job is being processed.'];
        } catch (\Exception $e) {
            DB::rollback();

            return [false, 'An error occurred - please try again later.'];
        }
    }
}
