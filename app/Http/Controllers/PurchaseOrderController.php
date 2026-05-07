<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'warehouse']);
        
        if ($request->filled('search')) {
            $query->where('po_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('supplier', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $purchaseOrders = $query->latest()->paginate(15);
        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('purchase-orders.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Generate unique PO number
        $poNumber = $this->generateUniquePONumber();

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'warehouse_id' => $request->warehouse_id,
                'status' => 'draft',
                'payment_status' => 'unpaid',
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
                
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $purchaseOrder->subtotal = $subtotal;
            $purchaseOrder->tax = $request->tax ?? 0;
            $purchaseOrder->shipping = $request->shipping ?? 0;
            $purchaseOrder->total = $subtotal + $purchaseOrder->tax + $purchaseOrder->shipping;
            $purchaseOrder->save();

            $this->logAudit('created', 'PurchaseOrder', $purchaseOrder->id, null, $purchaseOrder->toArray());

            DB::commit();
            return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create PO: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'warehouse', 'items.product']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $products = Product::all();
        $purchaseOrder->load('items');
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'status' => 'required|in:draft,pending,approved,partially_received,received,cancelled',
            'payment_status' => 'nullable|in:unpaid,partial,paid',
            'payment_method' => 'nullable|string|max:100',
        ]);

        $oldStatus = $purchaseOrder->status;
        $newStatus = $request->status;
        $oldValues = $purchaseOrder->toArray();

        DB::beginTransaction();
        try {
            $this->handleStatusTransition($purchaseOrder, $oldStatus, $newStatus);

            $purchaseOrder->update($request->only([
                'supplier_id',
                'warehouse_id',
                'order_date',
                'expected_delivery_date',
                'terms',
                'notes',
                'status',
                'payment_status',
                'payment_method',
            ]));

            $this->logAudit('updated', 'PurchaseOrder', $purchaseOrder->id, $oldValues, $purchaseOrder->toArray());

            DB::commit();
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'status' => 'required|in:draft,pending,approved,partially_received,received,cancelled',
            'payment_status' => 'nullable|in:unpaid,partial,paid',
            'payment_method' => 'nullable|string|max:100',
        ]);

        $oldStatus = $purchaseOrder->status;
        $newStatus = $request->status;
        $oldValues = $purchaseOrder->toArray();

        DB::beginTransaction();
        try {
            $this->handleStatusTransition($purchaseOrder, $oldStatus, $newStatus);

            $purchaseOrder->update($request->only(['status', 'payment_status', 'payment_method']));

            $this->logAudit('status_changed', 'PurchaseOrder', $purchaseOrder->id, $oldValues, $purchaseOrder->toArray());

            DB::commit();
            return redirect()->back()->with('success', 'Purchase order status updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Status update failed: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete purchase orders.');
        }

        DB::beginTransaction();
        try {
            // If PO was received, we need to subtract the stock that was added
            if (in_array($purchaseOrder->status, ['received', 'partially_received'])) {
                $this->reverseStockAddition($purchaseOrder);
            }

            $this->logAudit('deleted', 'PurchaseOrder', $purchaseOrder->id, $purchaseOrder->toArray(), null);

            $purchaseOrder->delete();

            DB::commit();
            return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────

    /**
     * Handle stock changes on PO status transitions.
     * received → add stock to products
     * cancelled (from received) → reverse the stock addition
     */
    private function handleStatusTransition(PurchaseOrder $purchaseOrder, string $oldStatus, string $newStatus): void
    {
        $receivedStatuses = ['received'];
        $wasReceived = in_array($oldStatus, $receivedStatuses);
        $isNowReceived = in_array($newStatus, $receivedStatuses);

        // Moving to "received" — add stock
        if ($isNowReceived && !$wasReceived) {
            $this->addStockFromPO($purchaseOrder);
        }

        // Moving from "received" to "cancelled" — reverse stock
        if ($wasReceived && $newStatus === 'cancelled') {
            $this->reverseStockAddition($purchaseOrder);
        }
    }

    /**
     * Add stock to products when PO is received.
     */
    private function addStockFromPO(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $item) {
            $product = $item->product;
            $product->quantity += $item->quantity_ordered;
            $product->save();

            StockTransaction::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'purchase_order_item_id' => $item->id,
                'type' => 'add',
                'quantity' => $item->quantity_ordered,
                'balance_after' => $product->quantity,
                'unit_cost' => $item->unit_price,
                'reference_number' => $purchaseOrder->po_number,
                'notes' => 'PO Received: ' . $purchaseOrder->po_number,
            ]);

            // Update the received quantity on the item
            $item->quantity_received = $item->quantity_ordered;
            $item->save();
        }
    }

    /**
     * Reverse stock addition (when cancelling a received PO).
     */
    private function reverseStockAddition(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $item) {
            $product = $item->product;
            $product->quantity -= $item->quantity_received ?? $item->quantity_ordered;
            $product->quantity = max(0, $product->quantity); // prevent negative
            $product->save();

            StockTransaction::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'remove',
                'quantity' => $item->quantity_received ?? $item->quantity_ordered,
                'balance_after' => $product->quantity,
                'reference_number' => $purchaseOrder->po_number,
                'notes' => 'PO Cancelled/Deleted — stock reversed: ' . $purchaseOrder->po_number,
            ]);
        }
    }

    /**
     * Generate a unique PO number with collision check.
     */
    private function generateUniquePONumber(): string
    {
        do {
            $number = 'PO-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }

    /**
     * Log an audit trail entry.
     */
    private function logAudit(string $action, string $model, int $modelId, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
