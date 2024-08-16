<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use bjoernffm\Spintax\Parser;

class Message extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getParsedMessage($url = null){
        $target_url = $this->target_url;
        $text = str_replace('[URL]', $url, $this->body);
        $spintax = Parser::parse($text);
        return $spintax->generate();
    }

    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }
}
