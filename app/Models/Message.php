<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getParsedMessage($url_shortener = null){
        $target_url = $this->target_url;
        if($url_shortener){
            // generate target url here
        }
        $text = str_replace('[link]', $target_url, $this->body);
        return $text;
    }
}
