<?php


namespace App\Warehouse\Application\GenerateSawlistFromBatchPicklist;

use App\Warehouse\Domain\Exceptions\BatchPicklistNotFoundException;
use App\Warehouse\Domain\Repositories\BatchPicklistRepositoryInterface;
use App\Warehouse\Domain\Repositories\PicklistRepositoryInterface;
use App\Warehouse\Domain\Services\ResourcePlanningServiceInterface;
use Illuminate\Support\Facades\Storage;

class GenerateSawlistFromBatchPicklist implements GenerateSawlistFromBatchPicklistInterface
{
    protected ResourcePlanningServiceInterface $resourcePlanningService;
    protected BatchPicklistRepositoryInterface $batchPicklistRepository;
    protected PicklistRepositoryInterface $picklistRepository;

    /**
     * GenerateSawlistFromBatchPicklist constructor.
     */
    public function __construct(ResourcePlanningServiceInterface $resourcePlanningService,
                                BatchPicklistRepositoryInterface $batchPicklistRepository,
                                PicklistRepositoryInterface $picklistRepository)
    {
        $this->resourcePlanningService = $resourcePlanningService;
        $this->batchPicklistRepository = $batchPicklistRepository;
        $this->picklistRepository = $picklistRepository;
    }

    /**
     * @inheritDoc
     * @throws BatchPicklistNotFoundException
     */
    public function execute(GenerateSawlistFromBatchPicklistInput $input): GenerateSawlistFromBatchPicklistResult
    {
        $batchPicklistId = $input->batchPicklistId();
        $batchPicklist = $this->batchPicklistRepository->findOneById($batchPicklistId);

        if (! $batchPicklist)
        {
            throw new BatchPicklistNotFoundException('Batch picklist with id ' . $batchPicklistId . ' not found');
        }

        $sawDrawings = $this->resourcePlanningService->produceGoods($batchPicklist->orderedItems(), '', '', '');

        $filePath = $sawDrawings['file_path'];
        $filePathWithBatchPicklistId = '/saw-drawings/zaaglijst_' . $batchPicklistId . '.pdf';
        $sawDrawings['file_path'] = Storage::path($filePathWithBatchPicklistId);

        if (Storage::exists($filePathWithBatchPicklistId))
        {
            Storage::delete($filePathWithBatchPicklistId);
        }

        Storage::move($filePath, $filePathWithBatchPicklistId);

        return new GenerateSawlistFromBatchPicklistResult($sawDrawings);
    }
}
