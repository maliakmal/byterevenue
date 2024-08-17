<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use bjoernffm\Spintax\Parser;

class Message extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function sanitizeUrl($url) {
        // Parse the URL to check if it contains a scheme
        $parsedUrl = parse_url($url);
    
        // If no scheme is present, prepend "https://"
        if (!isset($parsedUrl['scheme'])) {
            return 'https://' . $url;
        }
    
        // If the scheme is "http", replace it with "https"
        if ($parsedUrl['scheme'] === 'http') {
            return 'https://' . substr($url, 7);
        }
    
        // If the scheme is already "https", return the URL as is
        return $url;
    }
    public function getParsedMessage($url = null){
        $target_url = ($this->target_url);

        $text = str_replace('[URL]', $this->sanitizeUrl($url), $this->body);
        $spintax = Parser::parse($text);
        return $spintax->generate();
    }

    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }
}
