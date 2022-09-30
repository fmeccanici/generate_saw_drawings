<?php

namespace App\Warehouse\Domain\Repositories;

use App\Warehouse\Domain\BatchPicklists\BatchPicklist;

interface BatchPicklistRepositoryInterface
{
    public function addOne(BatchPicklist $batchPicklist): BatchPicklist;
    public function findOneById(string|int $id): ?BatchPicklist;
}
