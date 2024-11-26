<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Contact;
use Illuminate\Support\Facades\Redis;

class ProcessRecipientsImportCsvChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $rows = [];
        $rows = isset($this->data['rows'])?$this->data['rows']:$rows;
        $user_id = isset($this->data['user_id'])?$this->data['user_id']:null;
        $end_import = isset($this->data['is_import'])?true:false;
        if(!isset($this->data['recipient_list'])){
            echo 'MISSING RECIPIENT LIST';
            exit();
        }

        if(!is_object($this->data['recipient_list'])){
            echo 'MISSING RECIPIENT LIST';
            exit();
        }
        $recipient_list = $this->data['recipient_list'];

        $_data = [];

        foreach($rows as $row){
            $_data[] = [
                'name' => empty($row[0]) ? $row[2] : $row[0],
                'phone' => $row[2],
                'email' => $row[1],
            ];
        }

        $insertables = [];
        $now = now()->toDateTimeString();
        $existing_phones_for_user = Contact::select()->where(['user_id'=>$user_id])->pluck('phone', 'id')->toArray();
        $attachables = [];
        foreach ($rows as $_row) {
            $row = [];
            $row['name'] = empty($_row[0]) ? $_row[2] : $_row[0];
            $row['phone'] = $_row[2];
            $row['email'] = $_row[1];
            if(is_array($row)):
                if(!in_array($row['phone'], $existing_phones_for_user)):
                    $insertables[] =[
                        'phone' => $row['phone'],
                        'user_id'=>$user_id,
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'created_at'=>$now,
                        'updated_at'=>$now,
                        ];
                else:
                    $attachable_id = array_search($row['phone'], $existing_phones_for_user);
                    echo 'ATTACHING'."\n";
                    $recipient_list->contacts()->attach($attachable_id, ['user_id'=>$user_id]);
                endif;

            else:

                $insertables[] =[
                    'phone' => $row,
                    'user_id'=>$user_id,
                    'name' => $row,
                    'email' => '',
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];

            endif;
        }

        foreach($insertables as $insertable){
            $contact = Contact::create($insertable);
            $recipient_list->contacts()->attach($contact->id, ['user_id'=>$user_id]);
        }

        if($end_import){
            $recipient_list->is_imported = true;
            $recipient_list->save();

        }




}
}
