<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RecipientsGroup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ids' => 'array',
    ];

    public function recipientsList()
    {
        return $this->belongsTo(RecipientsList::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param int $limit (max 65000)
     *
     * @return Collection
     */
    public function getLimitContacts($limit = 10000): Collection
    {
        $iterator = new \ArrayIterator($this->ids);
        $cutArray = iterator_to_array(new \LimitIterator($iterator, 0, $limit));

        return Contact::query()
            ->whereIn('id', $cutArray)
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection
     */
    public function getAllContacts(): Collection
    {
        $ids = $this->ids;
        $results = collect();

        collect($ids)->chunk(10000)->each(function ($chunk) use (&$results) {
            $partialResults = Contact::query()
                ->whereIn('id', $chunk)
                ->get();

            $results = $results->merge($partialResults);
        });

        return $results;
    }

    /**
     * This method is lightweight
     *
     * @return array
     */
    public function getAllContactsArray(): array
    {
        $ids = $this->ids;
        $results = [];

        collect($ids)->chunk(10000)->each(function ($chunk) use (&$results) {
            $partialResults = \DB::table('contacts')
                ->whereIn('id', $chunk)
                ->get()->toArray();

            $results = array_merge($results, $partialResults);
        });

        return $results;
    }
}
