<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastBatch extends Model
{
    const STATUS_DRAFT = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_DONE = 2;

    use HasFactory;
    protected $guarded = [];

    public function recipient_list(){
       return $this->belongsTo(RecipientsList::class, 'recipients_list_id');
    }
    
    public function message(){
        return $this->belongsTo(Message::class);
    }

    public function campaign(){
        return $this->belongsTo(Campaign::class);
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
