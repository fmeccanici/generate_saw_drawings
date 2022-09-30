<?php


namespace App\Warehouse\Application\GenerateSawlistFromBatchPicklist;


use Illuminate\Support\Collection;

final class GenerateSawlistFromBatchPicklistResult
{
    protected Collection $sawDrawings;

    /**
     * @param Collection $sawDrawings
     */
    public function __construct(Collection $sawDrawings)
    {
        $this->sawDrawings = $sawDrawings;
    }

    public function sawDrawings(): Collection
    {
        return $this->sawDrawings;
    }
}
