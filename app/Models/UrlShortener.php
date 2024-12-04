<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BatchFile;

class UrlShortener extends Model
{
    protected $fillable = [
        'name',
        'endpoint',
        'asset_id',
        'is_registered',
        'response',
        'is_propagated'
    ];

    public function campaignShortUrls()
    {
        return $this->hasMany(CampaignShortUrl::class, 'url_shortener_id');
    }

    public function scopeOnlyRegistered($q)
    {
        return $q->where('is_registered', 1);
    }

    /**
     * @param $query
     * @param $isPropagated
     * @return mixed
     */
    public function scopePropagatedFilter($query, $isPropagated)
    {
        return $query->where('is_propagated', $isPropagated);
    }

    /**
     * @param $query
     * @param $urlValue
     * @return mixed
     */
    public function scopeWhereUrl($query, $urlValue)
    {
        return $query->where('url', $urlValue);
    }
}
