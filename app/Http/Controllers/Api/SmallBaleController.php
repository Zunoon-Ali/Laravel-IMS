<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmallBale\StoreSmallBaleRequest;
use App\Http\Resources\SmallBaleResource;
use App\Models\SmallBale;
use App\Services\SmallBaleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmallBaleController extends Controller
{
    use ApiResponse;

    protected SmallBaleService $smallBaleService;

    /**
     * Inject SmallBaleService.
     */
    public function __construct(SmallBaleService $smallBaleService)
    {
        $this->smallBaleService = $smallBaleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $query = SmallBale::query();
        if ($category) {
            $query->where('category', $category);
        }
        $smallBales = $query->latest()->get();
        return $this->successResponse(
            SmallBaleResource::collection($smallBales), 
            'Small bales retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSmallBaleRequest $request): JsonResponse
    {
        $smallBale = $this->smallBaleService->storeSmallBale($request->validated());
        return $this->successResponse(
            new SmallBaleResource($smallBale), 
            'Small bale created successfully', 
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(SmallBale $smallBale): JsonResponse
    {
        $history = \App\Models\DailyProduction::where('name', $smallBale->name)->latest()->get();
        return $this->successResponse([
            'item' => new SmallBaleResource($smallBale),
            'history' => $history
        ], 'Small bale details retrieved');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSmallBaleRequest $request, SmallBale $smallBale): JsonResponse
    {
        $updated = $this->smallBaleService->updateSmallBale($smallBale, $request->validated());
        return $this->successResponse(
            new SmallBaleResource($updated), 
            'Small bale updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmallBale $smallBale): JsonResponse
    {
        $smallBale->delete();
        return $this->successResponse(null, 'Small bale deleted successfully');
    }

    /**
     * Store Daily Production Records (Batch)
     */
    public function storeProductionBatch(Request $request): JsonResponse
    {
        $request->validate([
            'productions' => 'required|array',
            'productions.*.name' => 'required|string',
            'productions.*.bales' => 'required|integer|min:1',
            'productions.*.weight' => 'required|numeric|max:5000',
            'productions.*.supplier' => 'nullable|string',
            'productions.*.date' => 'required|date',
        ]);

        $created = $this->smallBaleService->storeProductionBatch($request->productions);

        return $this->successResponse($created, 'Production records saved successfully', 201);
    }

    /**
     * Get Daily Production Pivot Table.
     */
    public function getDailyProductions(Request $request): JsonResponse
    {
        $category = $request->query('category', 'small-bales');
        $pivot = $this->smallBaleService->getDailyProductionsPivot($category);
        return $this->successResponse($pivot, 'Daily productions retrieved successfully');
    }

    /**
     * Get Daily Sales Pivot Table.
     */
    public function getDailySales(Request $request): JsonResponse
    {
        $category = $request->query('category', 'small-bales');
        $pivot = $this->smallBaleService->getDailySalesPivot($category);
        return $this->successResponse($pivot, 'Daily sales retrieved successfully');
    }

    /**
     * Upload an image file and return its public URL.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Ensure public/uploads directory exists
            $path = public_path('uploads');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $file->move($path, $filename);
            $url = url('uploads/' . $filename);
            
            return $this->successResponse(['url' => $url], 'Image uploaded successfully');
        }

        return $this->errorResponse('No image file provided', 400);
    }
}
