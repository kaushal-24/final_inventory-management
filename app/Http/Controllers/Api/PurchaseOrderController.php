<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'warehouse', 'items.product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $purchaseOrders
        ]);
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'user', 'warehouse', 'items.product'])->find($id);

        if (!$purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $purchaseOrder
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'supplier_id' => $request->supplier_id,
            'user_id' => Auth::id() ?? null,
            'warehouse_id' => $request->warehouse_id,
            'status' => 'draft',
            'order_date' => $request->order_date,
            'expected_delivery_date' => $request->expected_delivery_date,
            'terms' => $request->terms,
            'notes' => $request->notes,
            'currency' => $request->currency ?? 'USD',
        ]);

        $subtotal = 0;

        foreach ($request->items as $item) {
            $totalPrice = $item['quantity_ordered'] * $item['unit_price'];
            $subtotal += $totalPrice;

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $item['product_id'],
                'quantity_ordered' => $item['quantity_ordered'],
                'unit_price' => $item['unit_price'],
                'total_price' => $totalPrice,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $purchaseOrder->subtotal = $subtotal;
        $purchaseOrder->total = $subtotal + ($request->tax ?? 0) + ($request->shipping ?? 0);
        $purchaseOrder->save();

        return response()->json([
            'success' => true,
            'data' => $purchaseOrder->load('items.product'),
            'message' => 'Purchase order created successfully'
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::find($id);

        if (!$purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,pending,approved,partially_received,received,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $purchaseOrder->status = $request->status;
        $purchaseOrder->save();

        return response()->json([
            'success' => true,
            'data' => $purchaseOrder,
            'message' => 'Purchase order status updated successfully'
        ]);
    }
}
