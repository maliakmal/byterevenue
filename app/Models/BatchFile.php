<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchFile extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getBatchFromFilename(){
        preg_match('/byterevenue-[^\/]*-(.*?)\.csv/', $this->filename, $matches);
        if(!$matches[1]){
            return null;
        }else{
            return $matches[1];
        }        
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'batch_file_campaign');
    }


}
