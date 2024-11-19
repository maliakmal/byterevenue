<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRecipientsList extends Model
{
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
