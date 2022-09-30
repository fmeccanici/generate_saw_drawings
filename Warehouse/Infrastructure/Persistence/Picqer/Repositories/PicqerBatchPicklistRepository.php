<?php

namespace App\Warehouse\Infrastructure\Persistence\Picqer\Repositories;

use App\Warehouse\Domain\BatchPicklists\BatchPicklist;
use App\Warehouse\Domain\Exceptions\BatchPicklistNotFoundException;
use App\Warehouse\Domain\Exceptions\PickingContainerNotSpecifiedException;
use App\Warehouse\Domain\Orders\OrderedItem;
use App\Warehouse\Domain\Orders\ProductFactory;
use App\Warehouse\Infrastructure\ApiClients\PicqerApiClient;
use App\Warehouse\Infrastructure\Exceptions\PicqerBatchPicklistRepositoryOperationException;
use Illuminate\Support\Arr;

class PicqerBatchPicklistRepository implements \App\Warehouse\Domain\Repositories\BatchPicklistRepositoryInterface
{
    private \Picqer\Api\Client $apiClient;

    public function __construct(PicqerApiClient $picqerApiClient)
    {
        $this->apiClient = $picqerApiClient->getClient();
    }

    public function addOne(BatchPicklist $batchPicklist): BatchPicklist
    {
        // TODO: Implement addOne() method.
    }

    /**
     * @throws BatchPicklistNotFoundException
     * @throws PicqerBatchPicklistRepositoryOperationException
     * @throws PickingContainerNotSpecifiedException
     */
    public function findOneById(int|string $id): ?BatchPicklist
    {
        $apiResponse = $this->apiClient->getAllBatchPicklists();

        if (Arr::get($apiResponse, 'success'))
        {
            $batchPicklists = Arr::get($apiResponse, 'data');
            $batchPicklists = collect($batchPicklists);

            if ($batchPicklists->isEmpty())
            {
                throw new BatchPicklistNotFoundException('Batch picklist with id ' . $id . ' not found');
            }
        } else {
            $errorMessage = Arr::get($apiResponse, 'errormessage');
            throw new PicqerBatchPicklistRepositoryOperationException('Failed getting all batch picklists from Picqer, with error: ' . $errorMessage);
        }

        $batchPicklist = $batchPicklists->first(function (array $batchPicklist) use ($id) {
            return Arr::get($batchPicklist, 'picklist_batchid') === $id;
        });

        if ($batchPicklist === null)
        {
            throw new BatchPicklistNotFoundException("Batch picklist with id $id not found");
        }

        $idBatchPicklist = Arr::get($batchPicklist, 'idpicklist_batch');

        $apiResponse = $this->apiClient->getBatchPicklist($idBatchPicklist);

        if (Arr::get($apiResponse, 'success'))
        {
            $batchPicklist = Arr::get($apiResponse, 'data');
            $products = Arr::get($batchPicklist, 'products');
            $products = collect($products);
            $picklists = Arr::get($batchPicklist, 'picklists');
            $picklists = collect($picklists);

            $orderedItems = collect();

            $products->each(function (array $product) use ($idBatchPicklist, $picklists, $orderedItems) {
                $picklistsOfProduct = Arr::get($product, 'picklists');
                $picklistsOfProduct = collect($picklistsOfProduct);

                // Lengths in mm
                $picklistsOfProduct->each(function (array $picklistOfProduct) use ($orderedItems, $picklists, $product) {
                    $length = Arr::get($picklistOfProduct, 'amount');

                    $location = $picklists->first(function (array $picklist) use ($picklistOfProduct) {
                        return Arr::get($picklist, 'idpicklist') === Arr::get($picklistOfProduct, 'idpicklist');
                    })['picking_container'];

                    if ($location === null)
                    {
                        throw new PickingContainerNotSpecifiedException('Zorg ervoor dat alle picklijsten van de batch picklijst gekoppeld zijn aan de picking containers');
                    }

                    $location = $location['name'];

                    $product = ProductFactory::productWithProductGroup($product['productcode'], 'rails', [
                        'length' => $length
                    ]);

                    $orderedItem = new OrderedItem($product, 'OnStock', null, null, null, $location);
                    $orderedItem->changeAmount(1);
                    $orderedItem->changePickingContainer($location);
                    $orderedItems->push($orderedItem);
                });
            });

            $batchPicklist = new BatchPicklist($orderedItems);
        } else {
            $errorMessage = Arr::get($apiResponse, 'errormessage');
            throw new PicqerBatchPicklistRepositoryOperationException("Failed getting batch picklist with id $id from Picqer, with error: $errorMessage");
        }

        return $batchPicklist;
    }
}
