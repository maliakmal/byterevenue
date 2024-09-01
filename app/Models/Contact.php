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
        return $this->belongsToMany(RecipientsList::class, 'contact_recipient_list', 'contact_id', 'recipients_list_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(BroadcastLog::class, 'contact_id', 'id')->where('is_sent', true);
    }
    public function campaigns()
    {
        return $this->hasMany(BroadcastLog::class, 'contact_id', 'id')->groupBy('campaign_id');
    }
    public function blackListNumber()
    {
        return $this->hasMany(BlackListNumber::class, 'phone_number', 'phone');
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->sanitizePhoneNumber();
        });
    }

    /**
     * Sanitize the phone number by removing all non-numeric characters
     * and leading '00' if present.
     */
    public function sanitizePhoneNumber()
    {
        if (isset($this->attributes['phone'])) {
            // Remove all non-numeric characters
            $number = preg_replace('/\D/', '', $this->attributes['phone']);

            // Remove leading 00 from international numbers
            if (strpos($number, '00') === 0) {
                $number = substr($number, 2);
            }

            $this->attributes['phone'] = $number;
        }
    }



}
