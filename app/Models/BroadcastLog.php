<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BroadcastLog extends Model
{
    use HasFactory, HasUlids;

    const CACHE_STATUS_KEY = 'broadcast_log_updating';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $appends = ['is_blocked'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function isBlocked(): Attribute
    {
        return Attribute::make(
            get: fn () => BlackListNumber::where('phone_number', $this->recipient_phone)->exists() ? 1 : 0
        )->shouldCache();
    }

    public function scopeWithIsBlocked($query)
    {
        return $query->addSelect([
            '*',
            'is_blocked' => BlackListNumber::select(DB::raw('COUNT(*) > 0'))
                ->whereColumn('phone_number', 'broadcast_logs.recipient_phone')
                ->limit(1)
        ]);
    }
}
