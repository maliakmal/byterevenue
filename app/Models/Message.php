<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getParsedMessage(){
        $text = str_replace('[link]', $this->target_url, $this->body);
        return $text;
    }
}
