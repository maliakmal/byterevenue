<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignShortUrl extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function getDomainFromUrlShortener(){
        $url = explode('/', $this->url_shortener);
        return $url[0];
    }

}
