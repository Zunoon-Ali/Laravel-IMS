<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmallBale\StoreSmallBaleRequest;
use App\Models\SmallBale;
use App\Models\DailyProduction;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmallBaleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $smallBales = SmallBale::latest()->paginate(10);
        return $this->successResponse($smallBales, 'Small bales retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSmallBaleRequest $request): JsonResponse
    {
        $smallBale = SmallBale::create($request->validated());
        return $this->successResponse($smallBale, 'Small bale created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SmallBale $smallBale): JsonResponse
    {
        return $this->successResponse($smallBale, 'Small bale details retrieved');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSmallBaleRequest $request, SmallBale $smallBale): JsonResponse
    {
        $smallBale->update($request->validated());
        return $this->successResponse($smallBale, 'Small bale updated successfully');
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
            'productions.*.bales' => 'required|integer',
            'productions.*.weight' => 'required|numeric',
            'productions.*.supplier' => 'nullable|string',
            'productions.*.date' => 'required|date',
        ]);

        $created = [];
        foreach ($request->productions as $prod) {
            $created[] = DailyProduction::create($prod);
        }

        return $this->successResponse($created, 'Production records saved successfully', 201);
    }
}
