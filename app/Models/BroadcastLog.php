<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastLog extends Model
{
    use HasFactory;

    const CACHE_STATUS_KEY = 'broadcast_log_updated';

    protected $guarded = [];

    protected $casts = [
        'keitaro_click_log' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }
    public function message(){
        return $this->belongsTo(Message::class);
    }

}
