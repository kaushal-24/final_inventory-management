<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function stockLevels(Request $request)
    {
        $query = Product::with(['category', 'warehouseStocks.warehouse']);

        if ($request->has('low_stock') && $request->low_stock) {
            $query->whereColumn('quantity', '<=', 'min_stock_level');
        }

        if ($request->has('out_of_stock') && $request->out_of_stock) {
            $query->where('quantity', 0);
        }

        $products = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function updateStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:add,remove',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'batch_id' => 'nullable|exists:batches,id',
            'notes' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        $qty = $request->quantity;
        $type = $request->action;

        if ($type == 'add') {
            $product->quantity += $qty;
        } else {
            if ($product->quantity < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ], 400);
            }
            $product->quantity -= $qty;
        }

        $product->save();

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => Auth::id() ?? null,
            'warehouse_id' => $request->warehouse_id,
            'batch_id' => $request->batch_id,
            'type' => $type,
            'quantity' => $qty,
            'balance_after' => $product->quantity,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'transaction' => $transaction
            ],
            'message' => 'Stock updated successfully'
        ]);
    }

    public function transactions(Request $request)
    {
        $query = StockTransaction::with(['product', 'user', 'warehouse', 'batch']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
