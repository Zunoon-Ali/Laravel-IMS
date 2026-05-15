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
            'productions.*.opened' => 'required|integer',
            'productions.*.date' => 'required|date',
        ]);

        $created = [];
        foreach ($request->productions as $prod) {
            $created[] = OpenedBale::create([
                'containerNo' => $prod['containerNo'],
                'opened' => $prod['opened'],
                'date' => $prod['date'],
                // Add logic for remaining, stockLbs etc based on container if needed
                'remaining' => 0,
                'stockLbs' => 0,
                'remainingLbs' => 0,
                'openValue' => 0,
                'remainingValue' => 0,
            ]);
        }

        return $this->successResponse($created, 'Opened bales records saved', 201);
    }
}
