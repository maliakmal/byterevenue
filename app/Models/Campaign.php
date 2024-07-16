<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tuupola\Base62;

class Campaign extends Model
{
    use HasFactory;
    protected $guarded = [];
    const STATUS_DRAFT = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_DONE = 2;

    public function user()
    {
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function broadcast_batches(){
        return $this->hasMany(BroadcastBatch::class);
    }
    public function recipient_list(){
        return $this->belongsTo(RecipientsList::class, 'recipients_list_id');
     }
     
    public function message(){
        return $this->hasOne(Message::class);
    }
    public function isDispatched(){
        return $this->status == self::STATUS_DRAFT ? false : true;
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

    public function markAsProcessed(){
        $this->status = self::STATUS_PROCESSING;
        $this->save();
    }


}
