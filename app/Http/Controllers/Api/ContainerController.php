<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Container\StoreContainerRequest;
use App\Models\Container;
use App\Models\OpenedBale;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $containers = Container::latest()->get();
        return $this->successResponse($containers, 'Containers retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContainerRequest $request): JsonResponse
    {
        $container = Container::create($request->validated());
        return $this->successResponse($container, 'Container created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Container $container): JsonResponse
    {
        return $this->successResponse($container, 'Container details retrieved');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreContainerRequest $request, Container $container): JsonResponse
    {
        $container->update($request->validated());
        return $this->successResponse($container, 'Container updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Container $container): JsonResponse
    {
        $container->delete();
        return $this->successResponse(null, 'Container deleted successfully');
    }

    /**
     * Get Opened Bales
     */
    public function getOpenedBales(): JsonResponse
    {
        $openedBales = OpenedBale::latest()->get();
        return $this->successResponse($openedBales, 'Opened bales retrieved successfully');
    }

    /**
     * Store Opened Bales
     */
    public function storeOpenedBales(Request $request): JsonResponse
    {
        $request->validate([
            'productions' => 'required|array',
            'productions.*.containerNo' => 'required|string',
            'productions.*.opened' => 'required|numeric',
            'productions.*.date' => 'required|date',
        ]);

        $created = [];
        foreach ($request->productions as $prod) {
            $container = Container::where('no', $prod['containerNo'])->first();
            $opened = floatval($prod['opened']);

            if ($container && $container->bales > 0) {
                $totalOpenedBefore = OpenedBale::where('containerNo', $prod['containerNo'])->sum('opened');
                $remaining = max(0, floatval($container->bales) - $totalOpenedBefore - $opened);
                
                $lbsPerBale = floatval($container->weightLbs) / floatval($container->bales);
                $pricePerBale = floatval($container->price) / floatval($container->bales);

                $stockLbs = $opened * $lbsPerBale;
                $remainingLbs = $remaining * $lbsPerBale;
                $openValue = $opened * $pricePerBale;
                $remainingValue = $remaining * $pricePerBale;
            } else {
                $remaining = 0;
                $stockLbs = 0;
                $remainingLbs = 0;
                $openValue = 0;
                $remainingValue = 0;
            }

            $created[] = OpenedBale::create([
                'containerNo' => $prod['containerNo'],
                'opened' => $opened,
                'date' => $prod['date'],
                'remaining' => $remaining,
                'stockLbs' => $stockLbs,
                'remainingLbs' => $remainingLbs,
                'openValue' => $openValue,
                'remainingValue' => $remainingValue,
            ]);
        }

        return $this->successResponse($created, 'Opened bales records saved', 201);
    }

    /**
     * Update an opened bale record
     */
    public function updateOpenedBale(Request $request, OpenedBale $openedBale): JsonResponse
    {
        $request->validate([
            'opened' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $opened = floatval($request->opened);
        $container = Container::where('no', $openedBale->containerNo)->first();

        if ($container && $container->bales > 0) {
            // Recalculate based on updated opened amount
            $totalOpenedBefore = OpenedBale::where('containerNo', $openedBale->containerNo)
                                ->where('id', '!=', $openedBale->id)
                                ->sum('opened');
            
            $remaining = max(0, floatval($container->bales) - $totalOpenedBefore - $opened);
            $lbsPerBale = floatval($container->weightLbs) / floatval($container->bales);
            $pricePerBale = floatval($container->price) / floatval($container->bales);

            $stockLbs = $opened * $lbsPerBale;
            $remainingLbs = $remaining * $lbsPerBale;
            $openValue = $opened * $pricePerBale;
            $remainingValue = $remaining * $pricePerBale;
        } else {
            $remaining = 0;
            $stockLbs = 0;
            $remainingLbs = 0;
            $openValue = 0;
            $remainingValue = 0;
        }

        $openedBale->update([
            'opened' => $opened,
            'date' => $request->date,
            'remaining' => $remaining,
            'stockLbs' => $stockLbs,
            'remainingLbs' => $remainingLbs,
            'openValue' => $openValue,
            'remainingValue' => $remainingValue,
        ]);

        return $this->successResponse($openedBale, 'Opened bale updated successfully');
    }

    /**
     * Delete an opened bale record
     */
    public function destroyOpenedBale(OpenedBale $openedBale): JsonResponse
    {
        $openedBale->delete();
        return $this->successResponse(null, 'Opened bale deleted successfully');
    }
}
