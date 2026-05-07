<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Batch;

class InventoryValuationService
{
    const METHOD_FIFO = 'fifo';
    const METHOD_LIFO = 'lifo';
    const METHOD_WEIGHTED_AVERAGE = 'weighted_average';

    public function calculateFIFO($productId)
    {
        $batches = Batch::where('product_id', $productId)
            ->where('quantity_available', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $totalValue = 0;
        $totalQuantity = 0;

        foreach ($batches as $batch) {
            $totalValue += $batch->quantity_available * ($batch->unit_cost ?? 0);
            $totalQuantity += $batch->quantity_available;
        }

        return [
            'value' => $totalValue,
            'quantity' => $totalQuantity,
            'method' => self::METHOD_FIFO
        ];
    }

    public function calculateLIFO($productId)
    {
        $batches = Batch::where('product_id', $productId)
            ->where('quantity_available', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalValue = 0;
        $totalQuantity = 0;

        foreach ($batches as $batch) {
            $totalValue += $batch->quantity_available * ($batch->unit_cost ?? 0);
            $totalQuantity += $batch->quantity_available;
        }

        return [
            'value' => $totalValue,
            'quantity' => $totalQuantity,
            'method' => self::METHOD_LIFO
        ];
    }

    public function calculateWeightedAverage($productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return null;
        }

        $transactions = StockTransaction::where('product_id', $productId)
            ->where('type', 'add')
            ->whereNotNull('unit_cost')
            ->get();

        if ($transactions->isEmpty()) {
            return [
                'value' => $product->quantity * ($product->cost_price ?? $product->price),
                'quantity' => $product->quantity,
                'average_cost' => $product->cost_price ?? $product->price,
                'method' => self::METHOD_WEIGHTED_AVERAGE
            ];
        }

        $totalCost = 0;
        $totalQuantity = 0;

        foreach ($transactions as $transaction) {
            $totalCost += $transaction->quantity * $transaction->unit_cost;
            $totalQuantity += $transaction->quantity;
        }

        $averageCost = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
        $currentValue = $product->quantity * $averageCost;

        return [
            'value' => $currentValue,
            'quantity' => $product->quantity,
            'average_cost' => $averageCost,
            'total_cost_purchased' => $totalCost,
            'total_quantity_purchased' => $totalQuantity,
            'method' => self::METHOD_WEIGHTED_AVERAGE
        ];
    }

    public function calculate($productId, $method = self::METHOD_WEIGHTED_AVERAGE)
    {
        switch ($method) {
            case self::METHOD_FIFO:
                return $this->calculateFIFO($productId);
            case self::METHOD_LIFO:
                return $this->calculateLIFO($productId);
            case self::METHOD_WEIGHTED_AVERAGE:
            default:
                return $this->calculateWeightedAverage($productId);
        }
    }

    public static function getMethods(): array
    {
        return [
            self::METHOD_FIFO => 'First In, First Out (FIFO)',
            self::METHOD_LIFO => 'Last In, First Out (LIFO)',
            self::METHOD_WEIGHTED_AVERAGE => 'Weighted Average Cost',
        ];
    }
}
