<?php

namespace App\Services\Simcard;

use App\Models\SimCard;

class SimcardService
{
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        return SimCard::create($data);
    }

    /**
     * @param array $data
     * @param int $id
     *
     * @return SimCard
     */
    public function update(array $data, int $id)
    {
        $simcard = SimCard::findOrFail($id);
        $simcard->update($data);

        return $simcard;
    }
}
