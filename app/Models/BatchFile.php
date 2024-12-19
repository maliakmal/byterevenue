<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchFile extends Model
{
    const STATUS_COMPLETED = 1;
    const STATUS_ERROR = 2;
    const STATUS_GENERATED = 3;
    const STATUS_REGENERATED = 4;
    protected $guarded = [];

    protected $casts = [
        'campaign_ids' => 'array',
    ];

    public function getBatchFromFilename(){
        preg_match('/byterevenue-[^\/]*-(.*?)\.csv/', $this->filename, $matches);

        return  !$matches[1] ? null : $matches[1];
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'batch_file_campaign');
    }

    public function urlShortener()
    {
        return $this->belongsTo(UrlShortener::class);
    }
}
