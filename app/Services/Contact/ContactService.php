<?php

namespace App\Services\Contact;

use App\Models\BroadcastLog;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactService
{
    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getContacts(Request $request)
    {
        $user       = auth()->user();
        $perPage    = $request->input('per_page', 15);
        $name       = $request->input('name');
        $area_code  = $request->input('area_code', '');
        $status     = intval($request->input('status',-1));
        $phone      = $request->input('phone', '');
        $sortBy     = $request->input('sort_by', 'id');
        $sortOrder  = $request->input('sort_order', 'desc');

        $filter_phone = $area_code ?: '%';
        $filter_phone .= ($phone ?: '') . '%';

        $contacts = $user->hasRole('admin') ? Contact::query() : Contact::where('user_id', $user->id);
        $contacts = $contacts->withCount(['blackListNumber'])->with('recipientLists')
            ->when($phone || $area_code, function ($query) use ($filter_phone) {
                return $query->where('phone', 'like', $filter_phone);
            })
            ->when($name, function ($query, $name) {
                return $query->where('name', 'like', "%$name%");
            })
            ->when(in_array($status, [0, 1]), function ($query) use ($status) {
                return $query->having('black_list_number_count', $status ? '<' : '>=', 1);
            })
            ->orderBy($sortBy, $sortOrder)->paginate($perPage);

        foreach ($contacts as $contact) {
            $contact['sent_count'] = \DB::table('broadcast_logs')
                ->where('contact_id', $contact->id)
                ->where('is_sent', true)
                ->count();

            $contact['campaigns_count'] = intval($contact->recipientLists?->sum('campaigns_count'));
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

        $contact = Contact::with('recipientLists')->whereIn('id', $ids)->first();

        $campaigns = intval($contact->recipientLists?->sum('campaigns_count'));

        $recipientsLists = $contact->recipientLists->count();

        return [
            'sent' => $sent,
            'campaigns' => $campaigns,
            'recipientLists' => $recipientsLists,
        ];
    }
}
