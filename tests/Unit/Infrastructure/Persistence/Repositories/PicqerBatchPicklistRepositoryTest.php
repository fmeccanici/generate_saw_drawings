<?php

namespace Tests\Unit\Warehouse\Infrastructure\Persistence\Picqer\Repositories;

use App\Warehouse\Domain\Exceptions\BatchPicklistNotFoundException;
use App\Warehouse\Domain\Exceptions\PickingContainerNotSpecifiedException;
use App\Warehouse\Infrastructure\ApiClients\PicqerApiClient;
use App\Warehouse\Infrastructure\Exceptions\PicqerBatchPicklistRepositoryOperationException;
use App\Warehouse\Infrastructure\Persistence\Picqer\Repositories\PicqerBatchPicklistRepository;
use Mockery\MockInterface;
use Picqer\Api\Client;
use Tests\TestCase;
use Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists\PicqerBatchPicklistReturnedFromGetSinglePicklistBatchFactory;
use Tests\Unit\Warehouse\Mocks\Picqer\PicqerMock;

class PicqerBatchPicklistRepositoryTest extends TestCase
{
    protected Client $picqerMock;
    protected PicqerBatchPicklistRepository $picqerBatchPicklistRepository;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->picqerMock = new PicqerMock();

        $picqerApiClientMock = $this->mock(PicqerApiClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('getClient')
                ->once()
                ->andReturn($this->picqerMock);
       });
        $this->picqerBatchPicklistRepository = new PicqerBatchPicklistRepository($picqerApiClientMock);
    }

    /** @test */
    public function it_should_throw_not_found_exception_when_there_are_no_batch_picklists()
    {
        // Given
        $batchPicklistId = 'Unknown Batch Picklist Id';

        // Then
        $this->expectException(BatchPicklistNotFoundException::class);
        $this->expectErrorMessage('Batch picklist with id ' . $batchPicklistId . ' not found');

        // When
        $this->picqerBatchPicklistRepository->findOneById($batchPicklistId);
    }

    /** @test */
    public function it_should_throw_exception_when_get_all_batch_picklists_fails()
    {
        // Then
        $errorMessage = 'Test Error Message';
        $this->expectException(PicqerBatchPicklistRepositoryOperationException::class);
        $this->expectErrorMessage("Failed getting all batch picklists from Picqer, with error: {$errorMessage}");

        // Given
        $picqerMock = $this->mock(Client::class, function (MockInterface $mock) use ($errorMessage) {
            $mock->shouldReceive('getAllBatchPicklists')
                ->once()
                ->andReturn([
                    'success' => false,
                    'errormessage' => $errorMessage
                ]);
        });

        $picqerApiClientMock = $this->mock(PicqerApiClient::class, function (MockInterface $mock) use ($picqerMock) {
            $mock->shouldReceive('getClient')
                ->once()
                ->andReturn($picqerMock);
        });

        $picqerBatchPicklistRepository = new PicqerBatchPicklistRepository($picqerApiClientMock);

        // When
        $picqerBatchPicklistRepository->findOneById('Test Id');
    }

    /** @test */
    public function it_should_throw_not_found_exception_when_batch_picklist_with_id_is_not_present_but_there_are_other_batch_picklists()
    {
        // Given
        $existingBatchPicklistId = 1;
        $nonExistingBatchPicklistId = 2;

        $picqerMock = $this->mock(Client::class, function (MockInterface $mock) use ($existingBatchPicklistId) {
            $mock->shouldReceive('getAllBatchPicklists')
                ->once()
                ->andReturn([
                    'success' => true,
                    'data' => [
                        [
                            'picklist_batchid' => $existingBatchPicklistId
                        ]
                    ]
                ]);
        });

        $picqerApiClientMock = $this->mock(PicqerApiClient::class, function (MockInterface $mock) use ($picqerMock) {
            $mock->shouldReceive('getClient')
                ->once()
                ->andReturn($picqerMock);
        });

        // Then
        $this->expectException(BatchPicklistNotFoundException::class);
        $this->expectErrorMessage('Batch picklist with id ' . $nonExistingBatchPicklistId . ' not found');

        $picqerBatchPicklistRepository = new PicqerBatchPicklistRepository($picqerApiClientMock);

        // When
        $picqerBatchPicklistRepository->findOneById($nonExistingBatchPicklistId);
    }

    /** @test */
    public function it_should_throw_operation_exception_when_get_batch_picklist_fails()
    {
        // Given
        $batchPicklistId = 1;
        $errorMessage = 'Test Error Message';
        $picqerMock = $this->partialMock(PicqerMock::class, function (MockInterface $mock) use ($errorMessage, $batchPicklistId) {

            $mock->shouldReceive('getAllBatchPicklists')
                ->once()
                ->andReturn([
                    'data' => [
                        [
                            'idpicklist_batch' => $batchPicklistId,
                            'picklist_batchid' => $batchPicklistId
                        ]
                    ],
                    'success' => true
                ]);

            $mock->shouldReceive('getBatchPicklist')
                ->once()
                ->andReturn([
                    'success' => false,
                    'errormessage' => $errorMessage
                ]);
        });

        $picqerApiClientMock = $this->mock(PicqerApiClient::class, function (MockInterface $mock) use ($picqerMock) {
            $mock->shouldReceive('getClient')
                ->once()
                ->andReturn($picqerMock);
        });

        $picqerBatchPicklistRepository = new PicqerBatchPicklistRepository($picqerApiClientMock);


        // Then
        $this->expectException(PicqerBatchPicklistRepositoryOperationException::class);
        $this->expectErrorMessage("Failed getting batch picklist with id {$batchPicklistId} from Picqer, with error: {$errorMessage}");

        // When
        $picqerBatchPicklistRepository->findOneById($batchPicklistId);
    }

    /** @test */
    public function it_should_return_batch_picklist()
    {
        // Given
        $picqerBatchPicklistReturnedFromGetSinglePicklistBatch = PicqerBatchPicklistReturnedFromGetSinglePicklistBatchFactory::create();
        $picqerMock = $this->partialMock(PicqerMock::class, function (MockInterface $mock) use ($picqerBatchPicklistReturnedFromGetSinglePicklistBatch) {

            $mock->shouldReceive('getAllBatchPicklists')
                ->once()
                ->andReturn([
                    'data' => [
                        [
                            'idpicklist_batch' => $picqerBatchPicklistReturnedFromGetSinglePicklistBatch->idPicklistBatch(),
                            'picklist_batchid' => $picqerBatchPicklistReturnedFromGetSinglePicklistBatch->picklistBatchId()
                        ]
                    ],
                    'success' => true
                ]);

            $mock->shouldReceive('getBatchPicklist')
                ->once()
                ->andReturn([
                    'success' => true,
                    'data' => $picqerBatchPicklistReturnedFromGetSinglePicklistBatch->toArray()
                ]);
        });

        $picqerApiClientMock = $this->mock(PicqerApiClient::class, function (MockInterface $mock) use ($picqerMock) {
            $mock->shouldReceive('getClient')
                ->once()
                ->andReturn($picqerMock);
        });

        $picqerBatchPicklistRepository = new PicqerBatchPicklistRepository($picqerApiClientMock);

        // When
        $foundBatchPicklist = $picqerBatchPicklistRepository->findOneById($picqerBatchPicklistReturnedFromGetSinglePicklistBatch->picklistBatchId());

        // Then
        self::assertNotNull($foundBatchPicklist);
    }

    /** @test */
    public function it_should_throw_domain_exception_when_picking_container_is_null()
    {
        // Given
        $picqerBatchPicklistReturnedFromGetSinglePicklistBatch = PicqerBatchPicklistReturnedFromGetSinglePicklistBatchFactory::create(1, [
            'picking_container' => 'null'
        ]);

        $picqerMock = $this->partialMock(PicqerMock::class, function (MockInterface $mock) use ($picqerBatchPicklistReturnedFromGetSinglePicklistBatch) {

            $mock->shouldReceive('getAllBatchPicklists')
                ->once()
                ->andReturn([
                    'data' => [
                        [
                            'idpicklist_batch' => $picqerBatchPicklistReturnedFromGetSinglePicklistBatch->idPicklistBatch(),
                            'picklist_batchid' => $picqerBatchPicklistReturnedFromGetSinglePicklistBatch->picklistBatchId()
                        ]
                    ],
                    'success' => true
                ]);

            $mock->shouldReceive('getBatchPicklist')
                ->once()
                ->andReturn([
                    'success' => true,
                    'data' => $picqerBatchPicklistReturnedFromGetSinglePicklistBatch->toArray()
                ]);
        });

        $picqerApiClientMock = $this->mock(PicqerApiClient::class, function (MockInterface $mock) use ($picqerMock) {
            $mock->shouldReceive('getClient')
                ->once()
                ->andReturn($picqerMock);
        });

        $picqerBatchPicklistRepository = new PicqerBatchPicklistRepository($picqerApiClientMock);

        // Then
        $this->expectException(PickingContainerNotSpecifiedException::class);
        $this->expectErrorMessage('Zorg ervoor dat alle picklijsten van de batch picklijst gekoppeld zijn aan de picking containers');

        // When
        $picqerBatchPicklistRepository->findOneById($picqerBatchPicklistReturnedFromGetSinglePicklistBatch->picklistBatchId());
    }
}