<?php

namespace App\Services\Contact;

use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ContactService
{
    /**
     * @param User $user
     * @param $perPage
     * @param $name
     * @param $area_code
     * @param $phone
     *
     * @return Collection
     */
    public function getContacts(
        User $user,
        $perPage,
        $name,
        $area_code,
        $phone
    ) {
        $filter_phone = $area_code ?: '%';
        $filter_phone .= ($phone ?: '') .'%';

        $contacts = $user->hasRole('admin') ? Contact::query() : Contact::where('user_id', $user->id);
        $contacts = $contacts->withCount(['blackListNumber'])
            ->when($phone || $area_code, function ($query) use ($filter_phone) {
                return $query->where('phone', 'like', $filter_phone);
            })
            ->when($name, function ($query, $name) {
                return $query->where('name', $name);
            })->orderBy('id', 'desc')->paginate($perPage);

        return $contacts;
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function getInfo(array $ids)
    {
        $sent = BroadcastLog::whereIn('contact_id', $ids)
            ->where('is_sent', true)
            ->count();

        $campaigns = BroadcastLog::whereIn('contact_id', $ids)
            ->groupBy('campaign_id')
            ->count();

        $recipientLists = DB::table('contact_recipient_list')
            ->whereIn('contact_id', $ids)
            ->count();

        return [
            'sent' => $sent,
            'campaigns' => $campaigns,
            'recipientLists' => $recipientLists,
        ];
    }
}
