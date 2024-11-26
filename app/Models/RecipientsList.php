<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipientsList extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

//    public function contacts()
//    {
//        return $this->belongsToMany(Contact::class, 'contact_recipient_list', 'recipients_list_id', 'contact_id');
//    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function canBeDeleted()
    {
        return $this->campaigns()->count() > 0 ? false : true;
    }

    public function recipientsGroup()
    {
        return $this->hasOne(RecipientsGroup::class);
    }
}
