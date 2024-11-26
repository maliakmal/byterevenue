<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RecipientsGroup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ids' => 'array',
        'ready_at' => 'timestamp',
    ];

    public function recipientsList()
    {
        return $this->belongsTo(RecipientsList::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private function getCutArray($offset, $limit)
    {
        $iterator = new \ArrayIterator($this->ids);

        return iterator_to_array(new \LimitIterator($iterator, $offset, $limit));
    }

    /**
     * @param int $limit (max 65000)
     *
     * @return Collection
     */
    public function getLimitedContacts($limit = 10000, $offset = 0): Collection
    {
        $cutArray = $this->getCutArray($offset, $limit);

        return Contact::query()
            ->whereIn('id', $cutArray)
            ->take($limit)
            ->get();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $select
     *
     * @return array
     */
    public function getLimitedContactsArray($limit = 10000, $offset = 0, $select = ['*']): Array
    {
        $cutArray = $this->getCutArray($offset, $limit);

        return \DB::table('contacts')
            ->select($select)
            ->whereIn('id', $cutArray)
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * @return Collection
     */
    public function getAllContacts(): Collection
    {
        $results = collect();

        collect($this->ids)
            ->chunk(10000)->each(function ($chunk) use (&$results) {
                $partialResults = Contact::query()
                ->whereIn('id', $chunk)
                ->get();

            $results = $results->merge($partialResults);
        });

        return $results;
    }

    /**
     * @param int $perPage
     *
     * @return LengthAwarePaginator
     */
    public function getAllContactsPaginated($perPage = 15): LengthAwarePaginator
    {
        $page = request('page', 1);
        $offset = ($page - 1) * $perPage;
        $cutArray = $this->getCutArray($offset, $perPage);
        $total = $this->count;

        $contacts = Contact::query()
            ->whereIn('id', $cutArray)
            ->take($perPage)
            ->get();

        return new LengthAwarePaginator(
            $contacts,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ],
        );
    }

    /**
     * This method is lightweight version of getAllContacts method
     *
     * @return array
     */
    public function getAllContactsArray(): array
    {
        $results = [];

        collect($this->ids)->chunk(10000)->each(function ($chunk) use (&$results) {
            $partialResults = \DB::table('contacts')
                ->whereIn('id', $chunk)
                ->get()
                ->toArray();

            $results = array_merge($results, $partialResults);
        });

        return $results;
    }
}
