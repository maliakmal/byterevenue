<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateSentMessage extends Model
{
    const FOLDER_NAME = 'update_sent_messages';

    const STATUS_CREATED    = 0;
    const STATUS_PENDING    = 10;
    const STATUS_PROCESSING = 20;
    const STATUS_COMPLETED  = 30;
    const STATUS_FAILED     = 40;

    const STATUSES = [
        self::STATUS_CREATED,
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
    ];

    protected $guarded = [];


}
