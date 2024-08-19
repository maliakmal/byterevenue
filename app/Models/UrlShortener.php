<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BatchFile;

class UrlShortener extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'endpoint', 'asset_id','is_registered', 'response',
        'is_propagated'
    ];

    public function scopeOnlyRegistered($q){
        return $q->where('is_registered', 1);
    }

    public function campaignShortUrls(){
        return $this->hasMany(CampaignShortUrl::class, 'url_shortener_id');
    }
}
