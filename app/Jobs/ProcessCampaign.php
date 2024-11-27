<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Hidehalo\Nanoid\Client;

class ProcessCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    protected $campaign = null;
    protected $limit = 1000;
    protected $offset = 0;
    protected $user = null;
    private $nanoid;

    const QUEUE_KEY = 'campaign_contact_processing';

    /**
     * Create a new job instance.
     */
    public function __construct($params = [])
    {
        $this->nanoid = new Client();
        $this->user = $params['user'] ?? $this->user;
        $this->campaign = $params['campaign'] ?? $this->campaign;
        $this->limit = $params['limit'] ?? $this->limit;
        $this->offset = $params['offset'] ?? $this->offset;
        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::debug('Processing campaign: ' . $this->campaign->id);

        $data = [];
        $now  = now()->toDateTimeString();
        $user = $this->user;
        $campaign = $this->campaign;
        $recipientList = $campaign->recipient_list;
        $message  = $campaign->message;
        $contacts = $recipientList->recipientsGroup?->getLimitedContactsArray($this->limit, $this->offset, ['id', 'phone']) ?? [];

        foreach ($contacts as $contact) {
            $data[] = [
                'id' => Str::ulid(),
                'slug' => $this->nanoid->generateId(size: 8, mode: Client::MODE_DYNAMIC),
                'user_id' => $user->id,
                'recipients_list_id' => $recipientList->id,
                'message_id' => $message->id,
                'message_body' => $message->getParsedMessage($contact->phone),
                'recipient_phone' => $contact->phone,
                'contact_id' => $contact->id,
                'is_downloaded_as_csv' => 0,
                'campaign_id' => $campaign->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        unset($contacts);

        // Insert any remaining records in the batch
        if (!empty($data)) {
            DB::statement('ALTER TABLE broadcast_logs DISABLE KEYS');
            DB::table('broadcast_logs')->insert($data);
            DB::statement('ALTER TABLE broadcast_logs ENABLE KEYS');
        } else {
            \Log::error('No contacts found for campaign: ' . $campaign->id);
        }

        unset($data);

        gc_collect_cycles();
    }
}
