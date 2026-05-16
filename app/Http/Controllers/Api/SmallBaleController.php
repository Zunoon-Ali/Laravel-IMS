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
    public function index(Request $request): JsonResponse
    {
        $query = SmallBale::latest();

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $smallBales = $query->paginate(10);
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
        return $this->successResponse($smallBale->fresh(), 'Small bale updated successfully');
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
            'productions'           => 'required|array',
            'productions.*.name'    => 'required|string',
            'productions.*.bales'   => 'required|integer|min:0',
            'productions.*.sold'    => 'nullable|integer|min:0',
            'productions.*.weight'  => 'required|numeric|min:0',
            'productions.*.supplier'=> 'nullable|string',
            'productions.*.date'    => 'required|date',
        ]);

        $created = [];
        foreach ($request->productions as $prod) {
            $created[] = DailyProduction::create([
                'name'     => $prod['name'],
                'bales'    => $prod['bales'],
                'sold'     => $prod['sold'] ?? 0,
                'weight'   => $prod['weight'],
                'supplier' => $prod['supplier'] ?? null,
                'date'     => $prod['date'],
            ]);
        }

        return $this->successResponse($created, 'Production records saved successfully', 201);
    }

    /**
     * Get daily production records (paginated, with optional month filter)
     */
    public function getDailyProductions(Request $request): JsonResponse
    {
        $query = DailyProduction::latest('date');

        // Month filter: expects 'month' as YYYY-MM (e.g. 2026-03)
        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $query->whereYear('date', $parts[0])
                      ->whereMonth('date', $parts[1]);
            }
        }

        // Item name filter
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $productions = $query->paginate(10);
        return $this->successResponse($productions, 'Daily productions retrieved successfully');
    }

    /**
     * Get daily sales data aggregated from small_bales table
     * Groups by name and date, returns bales produced vs sold with remaining
     */
    public function getDailySales(Request $request): JsonResponse
    {
        $query = SmallBale::select(
                'name',
                'production as bales_produced',
                'sale as bales_sold',
                \DB::raw('(production - sale) as remaining_bales'),
                'date'
            )
            ->latest('date');

        // Month filter
        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $query->whereYear('date', $parts[0])
                      ->whereMonth('date', $parts[1]);
            }
        }

        // Item filter
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $sales = $query->paginate(10);
        return $this->successResponse($sales, 'Daily sales retrieved successfully');
    }
}
