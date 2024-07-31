<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlShortener extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'endpoint', 'asset_id','is_registered', 'response',
        'is_propagated'
    ];

    public function scopeOnlyRegistered($q){
        return $q->where('is_registered', 1);
    }
}
