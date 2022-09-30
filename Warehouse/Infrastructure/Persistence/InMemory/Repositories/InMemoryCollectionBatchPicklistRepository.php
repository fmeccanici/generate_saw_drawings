<?php

namespace App\Warehouse\Infrastructure\Persistence\InMemory\Repositories;

use App\Warehouse\Domain\BatchPicklists\BatchPicklist;
use App\Warehouse\Domain\Repositories\BatchPicklistRepositoryInterface;
use Illuminate\Support\Collection;

class InMemoryCollectionBatchPicklistRepository implements BatchPicklistRepositoryInterface
{
    private Collection $batchPicklists;

    public function __construct()
    {
        $this->batchPicklists = collect();
    }

    public function addOne(BatchPicklist $batchPicklist): BatchPicklist
    {
        $this->batchPicklists->add($batchPicklist);
        return $batchPicklist;
    }

    public function findOneById(string|int $id): ?BatchPicklist
    {
        return $this->batchPicklists->first(function (BatchPicklist $batchPicklist) use ($id) {
            return $batchPicklist->identity() == $id;
        });
    }
}
