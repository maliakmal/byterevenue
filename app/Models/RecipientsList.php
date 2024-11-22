<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RecipientsList extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_recipient_list', 'recipients_list_id', 'contact_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function canBeDeleted()
    {
        return $this->campaigns()->count() > 0 ? false : true;
    }

    public function ContactGroup()
    {
        return $this->hasOne(RecipientsGroup::class);
    }

    public function getContactsIds(): array
    {
        $ids = [];

        \DB::table('contact_recipient_list')
            ->where('recipients_list_id', $this->id)
            ->select('id','contact_id')
            ->chunkById('10000', function (Collection $chunk) use (&$ids) {
                $ids = array_merge($ids, $chunk->pluck('contact_id')->toArray());
            });

        return $ids;
    }
}
