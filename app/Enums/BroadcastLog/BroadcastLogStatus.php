<?php

namespace App\Enums\BroadcastLog;

enum BroadcastLogStatus : string
{
    case DRAFT = 'draft';
    case SENT = 'send';
    case UNSENT = 'unsend';
}
