<?php

namespace App\Repositories\Contract\Contact;

use App\Repositories\Contract\BaseRepositoryInterface;

interface ContactRepositoryInterface extends BaseRepositoryInterface
{
    public function getBlockedListUserContacts(int $userID, ?int $perPage);
}
