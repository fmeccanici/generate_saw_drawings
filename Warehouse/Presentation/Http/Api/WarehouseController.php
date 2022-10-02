<?php


namespace App\Warehouse\Presentation\Http\Api;


use App\Warehouse\Application\GenerateSawlistFromBatchPicklist\GenerateSawlistFromBatchPicklist;
use App\Warehouse\Application\GenerateSawlistFromBatchPicklist\GenerateSawlistFromBatchPicklistInput;
use App\Warehouse\Domain\Repositories\BatchPicklistRepositoryInterface;
use App\Warehouse\Domain\Repositories\PicklistRepositoryInterface;
use App\Warehouse\Domain\Services\ResourcePlanningServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WarehouseController
{
    protected BatchPicklistRepositoryInterface $batchPicklistRepository;
    protected ResourcePlanningServiceInterface $resourcePlanningService;
    protected PicklistRepositoryInterface $picklistRepository;

    public function __construct()
    {
        $this->resourcePlanningService = App::make(ResourcePlanningServiceInterface::class);
        $this->batchPicklistRepository = App::make(BatchPicklistRepositoryInterface::class);
        $this->picklistRepository = App::make(PicklistRepositoryInterface::class);
    }

    public function generateSawListFromBatchPicklist(Request $request, int $batchPicklistId)
    {
        $generateSawListFromBatchPicklist = new GenerateSawlistFromBatchPicklist($this->resourcePlanningService, $this->batchPicklistRepository, $this->picklistRepository);
        $generateSawListFromBatchPicklistInput = new GenerateSawlistFromBatchPicklistInput([
            'batch_picklist_id' => $batchPicklistId
        ]);

        $result = $generateSawListFromBatchPicklist->execute($generateSawListFromBatchPicklistInput);
        $sawDrawings = $result->sawDrawings();
        $filePath = $sawDrawings['file_path'];

        return response()->download($filePath);
    }
}
