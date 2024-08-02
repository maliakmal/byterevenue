<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchFile extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getBatchFromFilename(){
        return preg_replace('/[^0-9]/', '', $this->filename);
    }
}
