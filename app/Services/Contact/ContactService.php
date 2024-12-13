<?php

namespace App\Services\Contact;

use App\Models\BroadcastLog;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class ContactService
{
    /**
     * @param User $user
     * @param $perPage
     * @param $name
     * @param $area_code
     * @param $phone
     * @param $status
     *
     * @return LengthAwarePaginator
     */
    public function getContacts(
        Request $request,
    ) {
        $user       = auth()->user();
        $perPage    = $request->input('per_page', 15);
        $name       = $request->input('name');
        $area_code  = $request->input('area_code', '');
        $status     = $request->input('status');
        $phone      = $request->input('phone', '');
        $sortBy     = $request->input('sort_by', 'id');
        $sortOrder  = $request->input('sort_order', 'desc');

        $filter_phone = $area_code ?: '%';
        $filter_phone .= ($phone ?: '') .'%';

        $contacts = $user->hasRole('admin') ? Contact::query() : Contact::where('user_id', $user->id);
        $contacts = $contacts->withCount(['blackListNumber'])
            ->when($phone || $area_code, function ($query) use ($filter_phone) {
                return $query->where('phone', 'like', $filter_phone);
            })
            ->when($name, function ($query, $name) {
                return $query->where('name', 'like', "%$name%");
            })
            ->when($status, function ($query, $status) {
                return $query->having('black_list_number_count', $status ? '<' : '>=', 1);
            })
            ->orderBy($sortBy, $sortOrder)->paginate($perPage);

        foreach ($contacts as $contact) {
            $info = $this->getInfo([$contact->id]);
            $contact['sent_count'] = $info['sent'];
            $contact['campaigns_count'] = $info['campaigns'];
        }
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

        $campaigns = 0;//BroadcastLog::whereIn('contact_id', $ids)
//            ->groupBy('campaign_id')
//            ->count();

        // $recipientLists = DB::table('contact_recipient_list')
        //     ->whereIn('contact_id', $ids)
        //     ->count();

        return [
            'sent' => $sent,
            'campaigns' => $campaigns,
            // 'recipientLists' => $recipientLists,
        ];
    }
}
