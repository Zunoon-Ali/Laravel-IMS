<?php

namespace App\Services;

use App\Models\Container;
use App\Models\OpenedBale;
use Illuminate\Support\Collection;

class ContainerService
{
    /**
     * Store a new container.
     */
    public function storeContainer(array $data): Container
    {
        $weightKg = floatval($data['weightKg']);
        $weightLbs = floatval($data['weightLbs']);
        $bales = intval($data['bales']);

        // Default actual_weight to weightKg if not provided
        $data['actual_weight'] = isset($data['actual_weight']) && !empty($data['actual_weight']) 
            ? floatval($data['actual_weight']) 
            : $weightKg;

        // Auto-calculate per bundle (lbs) = weight in (lbs) / total bales
        $data['per_bundle_lbs'] = $bales > 0 ? ($weightLbs / $bales) : 0.00;

        return Container::create($data);
    }

    /**
     * Update an existing container.
     */
    public function updateContainer(Container $container, array $data): Container
    {
        $weightKg = isset($data['weightKg']) ? floatval($data['weightKg']) : floatval($container->weightKg);
        $weightLbs = isset($data['weightLbs']) ? floatval($data['weightLbs']) : floatval($container->weightLbs);
        $bales = isset($data['bales']) ? intval($data['bales']) : intval($container->bales);

        if (isset($data['weightKg']) && (!isset($data['actual_weight']) || empty($data['actual_weight']))) {
            $data['actual_weight'] = $weightKg;
        }

        // Recalculate per bundle (lbs) if weight or bales changed
        $data['per_bundle_lbs'] = $bales > 0 ? ($weightLbs / $bales) : 0.00;

        $container->update($data);
        return $container;
    }

    /**
     * Store a batch of opened bales.
     */
    public function storeOpenedBales(array $productions): array
    {
        $containerNos = collect($productions)->pluck('containerNo')->unique()->toArray();

        // N+1 Prevention: Batch fetch all containers involved
        $containers = Container::whereIn('no', $containerNos)->get()->keyBy('no');

        // N+1 Prevention: Batch fetch all previously opened bale sums for these containers
        $openedSums = OpenedBale::whereIn('containerNo', $containerNos)
            ->groupBy('containerNo')
            ->selectRaw('containerNo, SUM(opened) as total_opened')
            ->pluck('total_opened', 'containerNo');

        $createdRecords = [];

        foreach ($productions as $prod) {
            $containerNo = $prod['containerNo'];
            $opened = floatval($prod['opened']);
            $date = $prod['date'];

            $container = $containers->get($containerNo);

            if ($container && $container->bales > 0) {
                // Sum of opened bales before this entry (O(1) lookup in-memory)
                $totalOpenedBefore = floatval($openedSums->get($containerNo, 0));

                // Bales Remaining = total bales - total opened before - bales opened now
                $remaining = max(0, floatval($container->bales) - $totalOpenedBefore - $opened);
                
                // Get per bundle (lbs) from container (falls back to calculation if needed)
                $perBundleLbs = floatval($container->per_bundle_lbs ?: ($container->weightLbs / $container->bales));

                // Stock open (lbs) = Bales opened * per bundle (lbs)
                $stockLbs = $opened * $perBundleLbs;

                // Remaining Stock (lbs) = Bales Remaining * per bundle (lbs)
                $remainingLbs = $remaining * $perBundleLbs;

                // Single bale price = container price / total container bales
                $singleBalePrice = floatval($container->bales) > 0 ? (floatval($container->price) / floatval($container->bales)) : 0.0;

                // Open Stock Value = bales opened now * single bale price
                $openValue = $opened * $singleBalePrice;

                // Remaining Stock Value = Bales Remaining * single bale price
                $remainingValue = $remaining * $singleBalePrice;

                // Update in-memory sums to handle multiple items for same container in same batch request
                $openedSums->put($containerNo, $totalOpenedBefore + $opened);
            } else {
                $remaining = 0;
                $stockLbs = 0;
                $remainingLbs = 0;
                $openValue = 0;
                $remainingValue = 0;
            }

            $createdRecords[] = OpenedBale::create([
                'container_id' => $container ? $container->id : null,
                'containerNo' => $containerNo,
                'date' => $date,
                'opened' => $opened,
                'remaining' => $remaining,
                'stockLbs' => $stockLbs,
                'remainingLbs' => $remainingLbs,
                'openValue' => $openValue,
                'remainingValue' => $remainingValue,
            ]);
        }

        return $createdRecords;
    }

    /**
     * Update an opened bale record.
     */
    public function updateOpenedBale(OpenedBale $openedBale, array $data): OpenedBale
    {
        $opened = floatval($data['opened']);
        $date = $data['date'];
        
        $container = Container::where('no', $openedBale->containerNo)->first();

        if ($container && $container->bales > 0) {
            // Recalculate based on updated opened amount, excluding current record
            $totalOpenedBefore = OpenedBale::where('containerNo', $openedBale->containerNo)
                                ->where('id', '!=', $openedBale->id)
                                ->sum('opened');
            
            // Bales Remaining = total bales - total opened before - bales opened now
            $remaining = max(0, floatval($container->bales) - $totalOpenedBefore - $opened);
            
            // Get per bundle (lbs)
            $perBundleLbs = floatval($container->per_bundle_lbs ?: ($container->weightLbs / $container->bales));

            // Stock open (lbs) = Bales opened * per bundle (lbs)
            $stockLbs = $opened * $perBundleLbs;

            // Remaining Stock (lbs) = Bales Remaining * per bundle (lbs)
            $remainingLbs = $remaining * $perBundleLbs;

            // Single bale price = container price / total container bales
            $singleBalePrice = floatval($container->bales) > 0 ? (floatval($container->price) / floatval($container->bales)) : 0.0;

            // Open Stock Value = bales opened * single bale price
            $openValue = $opened * $singleBalePrice;

            // Remaining Stock Value = Bales Remaining * single bale price
            $remainingValue = $remaining * $singleBalePrice;
        } else {
            $remaining = 0;
            $stockLbs = 0;
            $remainingLbs = 0;
            $openValue = 0;
            $remainingValue = 0;
        }

        $openedBale->update([
            'opened' => $opened,
            'date' => $date,
            'remaining' => $remaining,
            'stockLbs' => $stockLbs,
            'remainingLbs' => $remainingLbs,
            'openValue' => $openValue,
            'remainingValue' => $remainingValue,
        ]);

        return $openedBale;
    }
}
