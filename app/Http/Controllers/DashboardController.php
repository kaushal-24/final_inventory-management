<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use App\Models\Batch;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic KPIs
        $totalProducts = Product::count();
        $totalSuppliers = Supplier::count();
        $totalWarehouses = Warehouse::count();
        $lowStockCount = Product::whereColumn('quantity', '<=', 'min_stock_level')->count();
        $totalInventoryValue = Product::selectRaw('SUM(price * quantity) as total')->value('total') ?? 0;

        // Recent stock transactions
        $recentTransactions = StockTransaction::with(['product', 'user', 'warehouse'])
            ->latest()
            ->limit(10)
            ->get();

        // Category distribution
        $categoryDistribution = Category::withCount('products')
            ->has('products')
            ->get()
            ->map(function($category) use ($totalProducts) {
                return [
                    'name' => $category->name,
                    'count' => $category->products_count,
                    'percentage' => $totalProducts > 0 ? ($category->products_count / $totalProducts) * 100 : 0
                ];
            });

        // Top moving products
        $topMovingProducts = Product::withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->limit(10)
            ->get();

        // Order stats
        $pendingSalesOrders = SalesOrder::where('status', 'pending')->count();
        $pendingPurchaseOrders = PurchaseOrder::where('status', 'pending')->count();
        $totalSalesValue = SalesOrder::where('status', '!=', 'cancelled')->sum('total');
        $totalPurchaseValue = PurchaseOrder::where('status', '!=', 'cancelled')->sum('total');

        // Expiring batches (next 30 days)
        $expiringSoonCount = Batch::where('quantity_available', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->count();

        return view('dashboard', compact(
            'totalProducts',
            'totalSuppliers',
            'totalWarehouses',
            'lowStockCount',
            'totalInventoryValue',
            'recentTransactions',
            'categoryDistribution',
            'topMovingProducts',
            'pendingSalesOrders',
            'pendingPurchaseOrders',
            'totalSalesValue',
            'totalPurchaseValue',
            'expiringSoonCount'
        ));
    }
}
