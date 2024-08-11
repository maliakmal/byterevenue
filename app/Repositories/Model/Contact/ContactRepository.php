<?php

namespace App\Repositories\Model\Contact;

use App\Models\BlackListNumber;
use App\Models\Contact;
use App\Repositories\Contract\Contact\ContactRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class ContactRepository extends BaseRepository implements ContactRepositoryInterface
{
    public function __construct(Contact $model)
    {
        $this->model = $model;
    }


    /**
     * @param int $userID
     * @param ?int $perPage
     * @return mixed
     */
    public function getBlockedListUserContacts(int $userID, ?int $perPage)
    {
        return $this->model->select('phone')->where('user_id', $userID)
            ->whereIn('phone', BlackListNumber::select('phone_number'))
            ->paginate($perPage);
    }
}
