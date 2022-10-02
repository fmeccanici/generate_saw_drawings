<?php

use App\Warehouse\Presentation\Http\Api\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("/picklists/{picklistId}/pdf", [WarehouseController::class, 'streamPicklistPdf'])->name('stream-picklist')->withoutMiddleware('auth:api');
Route::get("/picklists/batch/{batchPicklistId}/saw-list", [WarehouseController::class, 'generateSawListFromBatchPicklist'])->name('generate-saw-list-from-batch-picklist');
