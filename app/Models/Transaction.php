<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const TYPE_PURCHASE  = 'purchase';
    const TYPE_DEDUCTION = 'deduction';
    const TYPE_USAGE     = 'usage';
    const TYPE_HIDDEN_PURCHASE = 'hidden_purchase';
    const TYPE_HIDDEN_DEDUCTION = 'hidden_deduction';

    const TYPES = [
        self::TYPE_PURCHASE,
        self::TYPE_DEDUCTION,
        self::TYPE_USAGE,
        self::TYPE_HIDDEN_PURCHASE,
        self::TYPE_HIDDEN_DEDUCTION,
    ];

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
