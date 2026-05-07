<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function applyFilters(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('quantity', '<=', 'min_stock_level');
            } elseif ($request->stock_status === 'out') {
                $query->where('quantity', 0);
            } elseif ($request->stock_status === 'in') {
                $query->where('quantity', '>', 0)->whereColumn('quantity', '>', 'min_stock_level');
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->applyFilters($request);
        $products = $query->paginate(10);
        
        $categories = \App\Models\Category::all();
        $suppliers = \App\Models\Supplier::all();

        return view('reports.index', compact('products', 'categories', 'suppliers'));
    }

    public function export(Request $request)
    {
        $query = $this->applyFilters($request);
        $products = $query->get();
        
        $fileName = 'custom_inventory_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['SKU', 'Product Name', 'Category', 'Supplier', 'Unit Price (INR)', 'Quantity', 'Unit', 'Total Value (INR)'];

        $callback = function() use($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku,
                    $product->name,
                    $product->category->name,
                    $product->supplier->name ?? 'N/A',
                    $product->price,
                    $product->quantity,
                    $product->unit,
                    $product->price * $product->quantity
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
