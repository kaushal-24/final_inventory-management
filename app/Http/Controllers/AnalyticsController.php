<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function calculateInventoryTurnover($days = 365)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $costOfGoodsSold = StockTransaction::where('type', 'remove')
            ->where('created_at', '>=', $startDate)
            ->sum('unit_cost');
        
        $avgInventoryValue = Product::selectRaw('AVG(price * quantity) as avg_value')->value('avg_value');
        
        if ($avgInventoryValue > 0) {
            return $costOfGoodsSold / $avgInventoryValue;
        }
        
        return 0;
    }

    public function calculateABCClassification()
    {
        $products = Product::with('category')
            ->selectRaw('*, (price * quantity) as total_value')
            ->orderBy('total_value', 'desc')
            ->get();
            
        $totalValue = $products->sum('total_value');
        $cumulativeValue = 0;
        $classification = ['A' => [], 'B' => [], 'C' => []];
        
        foreach ($products as $product) {
            $cumulativeValue += $product->total_value;
            $percentage = ($cumulativeValue / $totalValue) * 100;
            
            if ($percentage <= 80) {
                $classification['A'][] = $product;
            } elseif ($percentage <= 95) {
                $classification['B'][] = $product;
            } else {
                $classification['C'][] = $product;
            }
        }
        
        return $classification;
    }

    public function calculateStockAging()
    {
        $agingBuckets = [
            '0-30 days' => 0,
            '31-60 days' => 0,
            '61-90 days' => 0,
            '91-180 days' => 0,
            'Over 180 days' => 0,
        ];
        
        $batches = Batch::with('product')->where('quantity_available', '>', 0)->get();
        
        foreach ($batches as $batch) {
            $daysOld = Carbon::parse($batch->created_at)->diffInDays(now());
            $value = $batch->quantity_available * ($batch->unit_cost ?? $batch->product->cost_price ?? $batch->product->price);
            
            if ($daysOld <= 30) {
                $agingBuckets['0-30 days'] += $value;
            } elseif ($daysOld <= 60) {
                $agingBuckets['31-60 days'] += $value;
            } elseif ($daysOld <= 90) {
                $agingBuckets['61-90 days'] += $value;
            } elseif ($daysOld <= 180) {
                $agingBuckets['91-180 days'] += $value;
            } else {
                $agingBuckets['Over 180 days'] += $value;
            }
        }
        
        return $agingBuckets;
    }

    public function calculateCarryingCost($percentage = 0.25)
    {
        $totalInventoryValue = Product::selectRaw('SUM(price * quantity) as total')->value('total');
        return $totalInventoryValue * $percentage;
    }

    public function demandForecast($productId, $months = 6)
    {
        $transactions = StockTransaction::where('product_id', $productId)
            ->where('type', 'remove')
            ->where('created_at', '>=', Carbon::now()->subMonths($months))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(quantity) as total_quantity')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_quantity', 'month')
            ->toArray();
            
        if (count($transactions) < 2) {
            return ['forecast' => 0, 'trend' => 'insufficient_data'];
        }
        
        $values = array_values($transactions);
        $n = count($values);
        
        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += ($i + 1) * $values[$i];
            $sumX2 += ($i + 1) * ($i + 1);
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        $nextMonth = $n + 1;
        $forecast = max(0, $slope * $nextMonth + $intercept);
        
        $trend = $slope > 0.1 ? 'increasing' : ($slope < -0.1 ? 'decreasing' : 'stable');
        
        return [
            'forecast' => round($forecast),
            'trend' => $trend,
            'slope' => $slope,
            'intercept' => $intercept,
            'historical_data' => $transactions
        ];
    }
}
