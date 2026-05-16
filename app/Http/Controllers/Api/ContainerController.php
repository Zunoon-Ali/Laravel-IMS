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
        $containers = Container::latest()->paginate(10);
        
        // Dynamic Stats calculation
        $stats = [
            'total_containers' => Container::count(),
            'total_stock_kg' => Container::sum('weightKg'),
            'total_stock_lbs' => Container::sum('weightLbs'),
            'inventory_value' => Container::sum('price'),
        ];

        return $this->successResponse([
            'containers' => $containers,
            'stats' => $stats
        ], 'Containers retrieved successfully');
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
        $openedBales = OpenedBale::latest()->paginate(10);
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
            'productions.*.opened' => 'required|integer',
            'productions.*.date' => 'required|date',
        ]);

        $created = [];
        foreach ($request->productions as $prod) {
            $container = Container::where('no', $prod['containerNo'])->first();
            
            $stockLbs = 0;
            $openValue = 0;
            $remaining = 0;
            $remainingLbs = 0;
            $remainingValue = 0;
            $containerId = null;

            if ($container) {
                $containerId = $container->id;
                $avgWeightLbs = $container->bales > 0 ? ($container->weightLbs / $container->bales) : 0;
                $avgPricePerBale = $container->bales > 0 ? ($container->price / $container->bales) : 0;

                $stockLbs = $prod['opened'] * $avgWeightLbs;
                $openValue = $prod['opened'] * $avgPricePerBale;

                // Total previously opened
                $totalOpenedSoFar = OpenedBale::where('containerNo', $prod['containerNo'])->sum('opened');
                $remaining = $container->bales - ($totalOpenedSoFar + $prod['opened']);
                
                $totalStockLbsSoFar = OpenedBale::where('containerNo', $prod['containerNo'])->sum('stockLbs');
                $remainingLbs = $container->weightLbs - ($totalStockLbsSoFar + $stockLbs);

                $totalValueSoFar = OpenedBale::where('containerNo', $prod['containerNo'])->sum('openValue');
                $remainingValue = $container->price - ($totalValueSoFar + $openValue);
            }

            $created[] = OpenedBale::create([
                'container_id' => $containerId,
                'containerNo' => $prod['containerNo'],
                'opened' => $prod['opened'],
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
     * Update Opened Bale
     */
    public function updateOpenedBale(Request $request, $id): JsonResponse
    {
        $openedBale = OpenedBale::findOrFail($id);
        
        $request->validate([
            'opened' => 'required|integer',
            'date' => 'required|date',
        ]);

        $container = Container::where('no', $openedBale->containerNo)->first();
        
        if ($container) {
            $avgWeightLbs = $container->bales > 0 ? ($container->weightLbs / $container->bales) : 0;
            $avgPricePerBale = $container->bales > 0 ? ($container->price / $container->bales) : 0;

            $openedBale->opened = $request->opened;
            $openedBale->date = $request->date;
            $openedBale->stockLbs = $request->opened * $avgWeightLbs;
            $openedBale->openValue = $request->opened * $avgPricePerBale;

            $totalOpenedOthers = OpenedBale::where('containerNo', $openedBale->containerNo)
                ->where('id', '!=', $id)
                ->sum('opened');
            
            $openedBale->remaining = $container->bales - ($totalOpenedOthers + $request->opened);
            
            $totalStockLbsOthers = OpenedBale::where('containerNo', $openedBale->containerNo)
                ->where('id', '!=', $id)
                ->sum('stockLbs');
            $openedBale->remainingLbs = $container->weightLbs - ($totalStockLbsOthers + $openedBale->stockLbs);

            $totalValueOthers = OpenedBale::where('containerNo', $openedBale->containerNo)
                ->where('id', '!=', $id)
                ->sum('openValue');
            $openedBale->remainingValue = $container->price - ($totalValueOthers + $openedBale->openValue);
        } else {
            $openedBale->opened = $request->opened;
            $openedBale->date = $request->date;
        }

        $openedBale->save();

        return $this->successResponse($openedBale, 'Opened bale record updated');
    }

    /**
     * Delete Opened Bale
     */
    public function destroyOpenedBale($id): JsonResponse
    {
        $openedBale = OpenedBale::findOrFail($id);
        $openedBale->delete();
        return $this->successResponse(null, 'Opened bale record deleted');
    }
}
