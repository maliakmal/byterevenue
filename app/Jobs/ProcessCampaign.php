<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Hidehalo\Nanoid\Client;
use Hidehalo\Nanoid\GeneratorInterface;

class ProcessCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $campaign = null;
    protected $limit = null;
    protected $offset = null;
    protected $user = null;
    private $nanoid;

    /**
     * Create a new job instance.
     */
    public function __construct($params = [])
    {
        //
        $this->nanoid = new Client();
        $this->user = $params['user'] ?? $this->user;
        $this->campaign = $params['campaign'] ?? $this->campaign;
        $this->limit = $params['limit'] ?? $this->limit;
        $this->offset = $params['offset'] ?? $this->offset;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recipientList = $this->campaign->recipient_list;

        $data = [];
        $now = now();
    
        foreach ($recipientList->contacts()->limit($this->limit)->offset($this->offset)->get() as $contact) {
            $message = $this->campaign->message;

            $data[] = [
                'id' => Str::ulid(),
                'slug' => $this->nanoid->generateId(size: 8, mode: Client::MODE_DYNAMIC),
                'user_id' => $this->user->id,
                'recipients_list_id' => $recipientList->id,
                'message_id' => $message->id,
                'message_body' => $message->getParsedMessage($contact->phone),
                'recipient_phone' => $contact->phone,
                'contact_id' => $contact->id,
                'is_downloaded_as_csv' => 0,
                'campaign_id' => $this->campaign->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    
        // Insert any remaining records in the batch
        if (!empty($data)) {
            DB::table('broadcast_logs')->insert($data);
        }
    
    }
}
