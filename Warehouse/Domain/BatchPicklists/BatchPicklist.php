<?php

namespace App\Warehouse\Domain\BatchPicklists;

use App\SharedKernel\CleanArchitecture\Entity;
use Illuminate\Support\Collection;

class BatchPicklist extends Entity
{
    protected Collection $orderedItems;

    /**
     * @param Collection $orderedItems
     */
    public function __construct(Collection $orderedItems)
    {
        $this->orderedItems = $orderedItems;
    }

    public function orderedItems(): Collection
    {
        return $this->orderedItems;
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        // TODO: Implement cascadeSetIdentity() method.
    }
}
