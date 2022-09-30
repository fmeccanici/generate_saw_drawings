<?php


namespace App\Warehouse\Application\GenerateSawlistFromBatchPicklist;


interface GenerateSawlistFromBatchPicklistInterface
{
    /**
     * @param GenerateSawlistFromBatchPicklistInput $input
     * @return GenerateSawlistFromBatchPicklistResult
     */
    public function execute(GenerateSawlistFromBatchPicklistInput $input): GenerateSawlistFromBatchPicklistResult;
}
