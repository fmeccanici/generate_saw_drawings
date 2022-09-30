<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Picqer\Api\Client;

class SawDrawingController extends Controller
{
    private Client $apiClient;
    private PDF $pdf;
    private ?int $line;
    private array $data;

    public function __construct()
    {
        $subDomain = config('picqer.subdomain');
        $apiKey = config('picqer.api_key');
        $this->apiClient = new Client($subDomain, $apiKey);
        $this->apiClient->enableRetryOnRateLimitHit();
        $this->apiClient->setUseragent("Web Services");

        $this->pdf = app('dompdf.wrapper');

        $this->line = null;
    }

    public function generateSawDrawingFromBatchPicklist(Request $request, int $batchPicklistId)
    {
        $apiResponse = $this->apiClient->getAllBatchPicklists();

        if (Arr::get($apiResponse, 'success'))
        {
            $batchPicklists = Arr::get($apiResponse, 'data');
            $batchPicklists = collect($batchPicklists);
        }

        $batchPicklist = $batchPicklists->first(function (array $batchPicklist) use ($batchPicklistId) {
            return Arr::get($batchPicklist, 'picklist_batchid') === $batchPicklistId;
        });

        $idBatchPicklist = Arr::get($batchPicklist, 'idpicklist_batch');

        $apiResponse = $this->apiClient->getBatchPicklist($idBatchPicklist);

        if (Arr::get($apiResponse, 'success'))
        {
            $batchPicklist = Arr::get($apiResponse, 'data');
            $products = Arr::get($batchPicklist, 'products');
            $products = collect($products);
            $picklists = Arr::get($batchPicklist, 'picklists');
            $picklists = collect($picklists);

            $products->each(function (array $product) use ($idBatchPicklist, $picklists) {
                $picklistsOfProduct = Arr::get($product, 'picklists');
                $picklistsOfProduct = collect($picklistsOfProduct);

                // Lengths in mm
                $lengths = collect();
                $picklistsOfProduct->each(function (array $picklistOfProduct) use ($lengths, $picklists) {
                    $length = Arr::get($picklistOfProduct, 'amount');

                    // TODO: Get picking container instead of alias
                    $location = $picklists->first(function (array $picklist) use ($picklistOfProduct) {
                        return Arr::get($picklist, 'idpicklist') === Arr::get($picklistOfProduct, 'idpicklist');
                    })['picking_container']['name'];

                    $lengths->push([
                        'location' => $location,
                        'length' => $length
                    ]);
                });

                $lengths = $lengths->sortByDesc(function (array $length) {
                    return $length['length'];
                })->values();

                // In mm
                $maxRailLength = 7000;
                $sawDrawings = collect();
                $sawDrawing = collect();

                $i = 0;

                while ($lengths->isNotEmpty())
                {
                    $length = $lengths[$i];
                    $sum = $sawDrawing->sum(function (array $length) {
                        return $length['length'];
                    });

                    if ($sum < $maxRailLength)
                    {
                        if ($sum + $length['length'] <= $maxRailLength)
                        {
                            $sawDrawing->push($length);
                            $lengths->forget($i);
                            $lengths = $lengths->values();
                            $i = 0;
                            continue;
                        }
                    }

                    $i++;

                    if ($i === $lengths->count())
                    {
                        $sawDrawings->push($sawDrawing);
                        $sawDrawing = collect();
                        $i = 0;
                    }
                }

                $sawDrawings = $sawDrawings->map(function (Collection $sawDrawing) {
                    $this->line = null;
                    return $sawDrawing->map(function (array $length) {
                        if ($this->line === null)
                        {
                            $length['line'] = $length['length'];
                            $this->line += $length['length'];
                        } else {
                            $length['line'] = $this->line + $length['length'];
                        }

                        return $length;
                    });
                });

                $totalQuantityOfRails = $sawDrawings->count();
                $sawedRailsInMm = $sawDrawings->sum(function (Collection $sawDrawing) {
                        return $sawDrawing->sum(function (array $length) {
                            return $length['length'];
                        });
                });

                $maxSawedRailsInMm = $maxRailLength * $totalQuantityOfRails;
                $sawLoss = round(($maxSawedRailsInMm - $sawedRailsInMm) / $maxSawedRailsInMm * 100, 2);

                $this->data = [
                    'saw_drawings' => $sawDrawings->toArray(),
                    'saw_loss' => $sawLoss,
                    'quantity_rails' => $totalQuantityOfRails
                ];



            });

        }

        $output = $this->pdf->loadView('saw-drawing', $this->data)->output();

        $pdf = $output;
        $filePath = '/saw-drawings/zaaglijst_' . $idBatchPicklist . '.pdf';
        Storage::put($filePath, $pdf);
        $path = Storage::path($filePath);

        return response()->download($path);

    }
}
