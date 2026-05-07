<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckReorderPoints extends Command
{
    protected $signature = 'app:check-reorder-points';
    protected $description = 'Check products for low stock and trigger reorder point alerts';

    public function handle()
    {
        $this->info('Checking reorder points...');
        
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'min_stock_level')
            ->with(['category', 'supplier'])
            ->get();
            
        $count = $lowStockProducts->count();
        
        if ($count > 0) {
            $this->warn("Found {$count} products that need reordering!");
            
            foreach ($lowStockProducts as $product) {
                $this->line("- {$product->name} (SKU: {$product->sku}): {$product->quantity} / {$product->min_stock_level}");
                
                Log::warning('Product needs reordering', [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_quantity' => $product->quantity,
                    'min_stock_level' => $product->min_stock_level,
                ]);
            }
        } else {
            $this->info('No products need reordering at this time.');
        }
        
        $this->info('Reorder point check complete!');
        return Command::SUCCESS;
    }
}
