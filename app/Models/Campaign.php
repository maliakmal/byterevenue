<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tuupola\Base62;

class Campaign extends Model
{
    use HasFactory;

    protected $guarded = [];

    const STATUS_TEMPLATE = -1; // tmp status for save
    const STATUS_DRAFT = 0; // created but not run process
    const STATUS_PROCESSING = 1; // in process (generating logs)
    const STATUS_DONE = 2; // completed sending (via csv)

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function generateTrackableUrl($short_url = null, $params = null){

        $params = is_array($params)?$params:[];

        if($short_url == null){
            $url = UrlShortener::select()->inRandomOrder()->first();
            $short_url = $url->name;
        }

        return $short_url.'/'.($this->getUniqueFolder()).(count($params)>0?'?'.(http_build_query($params)):'');
    }

    public function getUniqueFolder(){

        if($this->code == ''){
            $this->generateUniqueFolder();
            $this->save();
        }

        return $this->code;
    }

    public function generateUniqueFolder(){
        $base62 = new \Tuupola\Base62;
        $res = $base62->encode($this->id);
        $this->code = $res;
    }

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function broadcast_batches(){
        return $this->hasMany(BroadcastBatch::class);
    }

    public function recipient_list(){
        return $this->belongsTo(RecipientsList::class, 'recipients_list_id');
    }

    public function message()
    {
        return $this->hasOne(Message::class);
    }

    public function isDispatched(){
        return $this->status != self::STATUS_DRAFT;
    }

    public function canBeDeleted(){
        return $this->status == self::STATUS_DRAFT;
    }

    public function canBeProcessed(){
        return $this->status == self::STATUS_DRAFT;
    }

    public function isDraft(){
        return $this->status == self::STATUS_DRAFT;
    }

    public function markAsProcessed()
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'total_recipients' => $this->recipient_list?->recipientsGroup?->count ?? 0,
            'submitted_at' => now()
        ]);
    }

    /**
     * @return HasMany
     */
    public function broadCaseLogMessages(){
        return $this->hasMany(BroadcastLog::class, 'campaign_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function broadCaseLogMessagesSent(){
        return $this->hasMany(BroadcastLog::class, 'campaign_id', 'id')
            ->where('is_sent', true);
    }

    /**
     * @return HasMany
     */
    public function broadCaseLogMessagesUnSent()
    {
        return $this->hasMany(BroadcastLog::class, 'campaign_id', 'id')
            ->where('is_sent', false);
    }

    /**
     * @return HasMany
     */
    public function broadCaseLogMessagesClick()
    {
        return $this->hasMany(BroadcastLog::class, 'campaign_id', 'id')
            ->where('is_click', true);
    }

    /**
     * @return HasMany
     */
    public function broadCaseLogMessagesNotClick()
    {
        return $this->hasMany(BroadcastLog::class, 'campaign_id', 'id')
            ->where('is_click', false);
    }

//    rework this relationship to campaign_ids array in batch_files
//    public function batchFiles()
//    {
//        return $this->belongsToMany(BatchFile::class, 'batch_file_campaign');
//    }
}
