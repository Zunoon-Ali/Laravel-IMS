<?php

namespace App\Services;

use App\Models\SmallBale;
use App\Models\DailyProduction;
use Illuminate\Support\Facades\DB;

class SmallBaleService
{
    /**
     * Store a new small bale item.
     */
    public function storeSmallBale(array $data): SmallBale
    {
        $weight = floatval($data['weight'] ?? 0.0);
        $rate = floatval($data['rate'] ?? 0.0);
        $quantity = intval($data['quantity'] ?? 0);

        // Auto-calculate weight in Lbs
        $data['weight_lbs'] = $weight * 2.20462;

        // Auto-calculate total inventory amount
        $data['amount'] = $quantity * $weight * $rate;

        // Initial stock is equal to the added quantity
        $data['stock'] = $quantity;

        return SmallBale::create($data);
    }

    /**
     * Update an existing small bale item.
     */
    public function updateSmallBale(SmallBale $smallBale, array $data): SmallBale
    {
        $weight = isset($data['weight']) ? floatval($data['weight']) : floatval($smallBale->weight);
        $rate = isset($data['rate']) ? floatval($data['rate']) : floatval($smallBale->rate);
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : intval($smallBale->quantity);

        // Recalculate parameters
        $data['weight_lbs'] = $weight * 2.20462;
        $data['amount'] = $quantity * $weight * $rate;

        // Keep stock updated if quantity is explicitly changed
        if (isset($data['quantity'])) {
            $diff = $quantity - intval($smallBale->quantity);
            $data['stock'] = max(0, intval($smallBale->stock) + $diff);
        }

        $smallBale->update($data);
        return $smallBale;
    }

    /**
     * Store Daily Production Records (Batch)
     */
    public function storeProductionBatch(array $productionsData): array
    {
        return DB::transaction(function () use ($productionsData) {
            $createdRecords = [];

            // N+1 Prevention: Pre-fetch all small bales involved by their names
            $names = collect($productionsData)->pluck('name')->unique()->toArray();
            $smallBales = SmallBale::whereIn('name', $names)->get()->keyBy('name');

            foreach ($productionsData as $prod) {
                $name = $prod['name'];
                $bales = intval($prod['bales']);
                $weight = floatval($prod['weight']);
                $supplier = $prod['supplier'] ?? null;
                $date = $prod['date'];

                // Create the DailyProduction record
                $dailyProd = DailyProduction::create([
                    'name' => $name,
                    'bales' => $bales,
                    'weight' => $weight,
                    'supplier' => $supplier,
                    'date' => $date,
                ]);
                $createdRecords[] = $dailyProd;

                // Update the matching SmallBale item stock and production
                $smallBale = $smallBales->get($name);
                if ($smallBale) {
                    $newProduction = intval($smallBale->production) + $bales;
                    $newStock = intval($smallBale->stock) + $bales;
                    $newQuantity = intval($smallBale->quantity) + $bales;
                    $newAmount = $newStock * floatval($smallBale->weight) * floatval($smallBale->rate);

                    $smallBale->update([
                        'production' => $newProduction,
                        'stock' => $newStock,
                        'quantity' => $newQuantity,
                        'amount' => $newAmount,
                    ]);
                }
            }

            return $createdRecords;
        });
    }

    /**
     * Get Daily Productions formatted in Pivot representation for frontend table.
     */
    public function getDailyProductionsPivot(string $category = 'small-bales'): array
    {
        // 1. Fetch last 9 distinct dates of production in ascending order, joined to small_bales for category filtering
        $dates = DailyProduction::join('small_bales', 'daily_productions.name', '=', 'small_bales.name')
            ->where('small_bales.category', $category)
            ->select('daily_productions.date')
            ->distinct()
            ->orderBy('daily_productions.date', 'desc')
            ->take(9)
            ->pluck('daily_productions.date')
            ->reverse()
            ->values()
            ->toArray();

        // 2. Fetch distinct item names of this category
        $itemNames = DailyProduction::join('small_bales', 'daily_productions.name', '=', 'small_bales.name')
            ->where('small_bales.category', $category)
            ->distinct()
            ->pluck('daily_productions.name')
            ->toArray();

        // If no records or dates exist, fallback to date
        if (empty($dates)) {
            $dates = [date('Y-m-d')];
        }

        // Format dates into readable headers like "18-May"
        $headers = ['Date']; // First column header represents item name row header
        foreach ($dates as $date) {
            $headers[] = date('d-M', strtotime($date));
        }

        // 3. Construct rows
        $rows = [];
        foreach ($itemNames as $name) {
            $row = ['name' => $name];
            
            // Loop through last 9 dates
            for ($i = 0; $i < 9; $i++) {
                $cellKey = 'd' . ($i + 1);
                if (isset($dates[$i])) {
                    // Sum bales produced for this item on this date
                    $balesSum = DailyProduction::where('name', $name)
                        ->where('date', $dates[$i])
                        ->sum('bales');
                    $row[$cellKey] = strval($balesSum);
                } else {
                    $row[$cellKey] = '0';
                }
            }
            $rows[] = $row;
        }

        return [
            'headers' => $headers,
            'data' => $rows
        ];
    }

    /**
     * Get Daily Sales formatted in Pivot representation (mocked with 0 0 records)
     */
    public function getDailySalesPivot(string $category = 'small-bales'): array
    {
        // Fetch last 9 distinct dates from DailyProduction to sync headers
        $dates = DailyProduction::join('small_bales', 'daily_productions.name', '=', 'small_bales.name')
            ->where('small_bales.category', $category)
            ->select('daily_productions.date')
            ->distinct()
            ->orderBy('daily_productions.date', 'desc')
            ->take(9)
            ->pluck('daily_productions.date')
            ->reverse()
            ->values()
            ->toArray();

        if (empty($dates)) {
            $dates = [date('Y-m-d')];
        }

        // Format dates into readable headers like "18-May"
        $headers = ['Date'];
        foreach ($dates as $date) {
            $headers[] = date('d-M', strtotime($date));
        }

        // Fetch all unique SmallBale names of this category
        $smallBaleNames = SmallBale::where('category', $category)->pluck('name')->toArray();

        $rows = [];
        foreach ($smallBaleNames as $name) {
            $row = ['name' => $name];
            for ($i = 1; $i <= 9; $i++) {
                $row['d' . $i] = '0';
            }
            $rows[] = $row;
        }

        return [
            'headers' => $headers,
            'data' => $rows
        ];
    }
}
