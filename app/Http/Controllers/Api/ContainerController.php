<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Container\StoreContainerRequest;
use App\Http\Resources\ContainerResource;
use App\Http\Resources\OpenedBaleResource;
use App\Models\Container;
use App\Models\OpenedBale;
use App\Services\ContainerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    use ApiResponse;

    protected ContainerService $containerService;

    /**
     * Inject ContainerService.
     */
    public function __construct(ContainerService $containerService)
    {
        $this->containerService = $containerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $containers = Container::latest()->get();
        return $this->successResponse(
            ContainerResource::collection($containers), 
            'Containers retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContainerRequest $request): JsonResponse
    {
        $container = $this->containerService->storeContainer($request->validated());
        return $this->successResponse(
            new ContainerResource($container), 
            'Container created successfully', 
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Container $container): JsonResponse
    {
        return $this->successResponse(
            new ContainerResource($container), 
            'Container details retrieved'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreContainerRequest $request, Container $container): JsonResponse
    {
        $updatedContainer = $this->containerService->updateContainer($container, $request->validated());
        return $this->successResponse(
            new ContainerResource($updatedContainer), 
            'Container updated successfully'
        );
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
     * Get Opened Bales. Eager loaded container relationship to prevent N+1 queries.
     */
    public function getOpenedBales(): JsonResponse
    {
        $openedBales = OpenedBale::with('container')->latest()->get();
        return $this->successResponse(
            OpenedBaleResource::collection($openedBales), 
            'Opened bales retrieved successfully'
        );
    }

    /**
     * Store Opened Bales.
     */
    public function storeOpenedBales(Request $request): JsonResponse
    {
        $request->validate([
            'productions' => 'required|array',
            'productions.*.containerNo' => 'required|string',
            'productions.*.opened' => 'required|numeric|gt:0',
            'productions.*.date' => 'required|date',
        ]);

        $created = $this->containerService->storeOpenedBales($request->productions);
        return $this->successResponse(
            OpenedBaleResource::collection($created), 
            'Opened bales records saved', 
            201
        );
    }

    /**
     * Update an opened bale record.
     */
    public function updateOpenedBale(Request $request, OpenedBale $openedBale): JsonResponse
    {
        $request->validate([
            'opened' => 'required|numeric|gt:0',
            'date' => 'required|date',
        ]);

        $updated = $this->containerService->updateOpenedBale($openedBale, $request->only(['opened', 'date']));
        return $this->successResponse(
            new OpenedBaleResource($updated), 
            'Opened bale updated successfully'
        );
    }

    /**
     * Delete an opened bale record.
     */
    public function destroyOpenedBale(OpenedBale $openedBale): JsonResponse
    {
        $openedBale->delete();
        return $this->successResponse(null, 'Opened bale deleted successfully');
    }
}
