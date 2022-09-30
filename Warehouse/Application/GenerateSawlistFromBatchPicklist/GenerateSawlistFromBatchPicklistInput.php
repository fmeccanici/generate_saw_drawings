<?php


namespace App\Warehouse\Application\GenerateSawlistFromBatchPicklist;

use HomeDesignShops\LaravelDdd\Support\Input;
use Illuminate\Support\Arr;

final class GenerateSawlistFromBatchPicklistInput extends Input
{
    /**
     * @var array The PASVL validation rules
     */
    protected $rules = [
        'batch_picklist_id' => ':number :int'
    ];
    /**
     * @var array|\ArrayAccess|mixed
     */
    protected int $batchPicklistId;

    /**
     * GenerateSawlistFromBatchPicklistInput constructor.
     */
    public function __construct($input)
    {
        $this->validate($input);
        $this->batchPicklistId = Arr::get($input, 'batch_picklist_id');
    }

    public function batchPicklistId(): int
    {
        return $this->batchPicklistId;
    }
}
