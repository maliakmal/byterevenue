<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlackListWord extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = 'black_list_words';
}
