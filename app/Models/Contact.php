<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipientLists()
    {
        return $this->belongsToMany(RecipientList::class, 'contact_recipient_list', 'contact_id', 'recipients_list_id');
    }

}