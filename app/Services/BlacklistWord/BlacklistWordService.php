<?php

namespace App\Services\BlacklistWord;

use App\Repositories\Contract\BlackListWord\BlackListWordRepositoryInterface;

class BlacklistWordService
{
    public function __construct(
        private BlackListWordRepositoryInterface $blackListWordRepository
    ) {}

    /**
     * @param int $count
     *
     * @return mixed
     */
    public function list(int $count)
    {
        return $this->blackListWordRepository->paginate($count);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        $word_string = $data['word'];
        $list = collect(explode("\n", $word_string));
        $list = $list->reject(function ($item){
            return empty($item);
        });
        $list = $list->map(function ($item) {
            return ['word' => trim($item)];
        });

        return $this->blackListWordRepository->upsertWord($list->toArray());
    }
}
