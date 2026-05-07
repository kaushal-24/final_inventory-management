<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'user', 'warehouse']);
        
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        $salesOrders = $query->latest()->paginate(15);
        return view('sales-orders.index', compact('salesOrders'));
    }

    public function create()
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $customers = Customer::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('sales-orders.create', compact('customers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Validate stock availability before creating
        $stockErrors = [];
        foreach ($request->items as $index => $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->quantity < $item['quantity']) {
                $stockErrors[] = "Insufficient stock for \"{$product->name}\" — available: {$product->quantity}, requested: {$item['quantity']}";
            }
        }
        if (!empty($stockErrors)) {
            return redirect()->back()->withInput()->withErrors($stockErrors);
        }

        // Generate unique order number
        $orderNumber = $this->generateUniqueOrderNumber();

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create([
                'order_number' => $orderNumber,
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id(),
                'warehouse_id' => $request->warehouse_id,
                'status' => 'pending',
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'currency' => $request->currency ?? 'INR',
                'payment_status' => 'unpaid',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'terms' => $request->terms,
                'notes' => $request->notes,
            ]);

            $subtotal = 0;

            foreach ($request->items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $taxRate = $item['tax_rate'] ?? 0;
                $taxAmount = ($totalPrice - $discount) * ($taxRate / 100);
                $finalTotal = $totalPrice - $discount + $taxAmount;
                
                $subtotal += $totalPrice - $discount;
                
                $salesOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $discount,
                    'tax_rate' => $taxRate,
                    'total_price' => $finalTotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Deduct stock immediately
                $product = Product::find($item['product_id']);
                $product->quantity -= $item['quantity'];
                $product->save();

                StockTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'warehouse_id' => $request->warehouse_id,
                    'type' => 'remove',
                    'quantity' => $item['quantity'],
                    'balance_after' => $product->quantity,
                    'reference_number' => $orderNumber,
                    'notes' => 'Sales Order Created: ' . $orderNumber,
                ]);
            }

            $salesOrder->subtotal = $subtotal;
            $salesOrder->tax = $request->tax ?? 0;
            $salesOrder->shipping = $request->shipping ?? 0;
            $salesOrder->discount = $request->discount ?? 0;
            $salesOrder->total = $subtotal + $salesOrder->tax + $salesOrder->shipping - $salesOrder->discount;
            $salesOrder->save();

            // Audit log
            $this->logAudit('created', 'SalesOrder', $salesOrder->id, null, $salesOrder->toArray());

            DB::commit();
            return redirect()->route('sales-orders.index')->with('success', 'Sales order created successfully. Stock has been deducted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'user', 'warehouse', 'items.product']);
        return view('sales-orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $customers = Customer::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $salesOrder->load('items');
        return view('sales-orders.edit', compact('salesOrder', 'customers', 'warehouses', 'products'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'status' => 'required|in:draft,pending,processing,shipped,delivered,cancelled,returned',
            'payment_status' => 'nullable|in:unpaid,partial,paid',
            'payment_method' => 'nullable|string|max:100',
        ]);

        $oldStatus = $salesOrder->status;
        $newStatus = $request->status;
        $oldValues = $salesOrder->toArray();

        DB::beginTransaction();
        try {
            // Handle stock changes on status transitions
            $this->handleStatusTransition($salesOrder, $oldStatus, $newStatus);

            $salesOrder->update($request->only([
                'customer_id',
                'warehouse_id',
                'order_date',
                'expected_delivery_date',
                'status',
                'payment_status',
                'payment_method',
                'terms',
                'notes',
            ]));

            $this->logAudit('updated', 'SalesOrder', $salesOrder->id, $oldValues, $salesOrder->toArray());

            DB::commit();
            return redirect()->route('sales-orders.show', $salesOrder)->with('success', 'Sales order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, SalesOrder $salesOrder)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'status' => 'required|in:draft,pending,processing,shipped,delivered,cancelled,returned',
            'payment_status' => 'nullable|in:unpaid,partial,paid',
            'payment_method' => 'nullable|string|max:100',
        ]);

        $oldStatus = $salesOrder->status;
        $newStatus = $request->status;
        $oldValues = $salesOrder->toArray();

        DB::beginTransaction();
        try {
            $this->handleStatusTransition($salesOrder, $oldStatus, $newStatus);

            $salesOrder->update($request->only(['status', 'payment_status', 'payment_method']));

            $this->logAudit('status_changed', 'SalesOrder', $salesOrder->id, $oldValues, $salesOrder->toArray());

            DB::commit();
            return redirect()->back()->with('success', 'Sales order status updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Status update failed: ' . $e->getMessage());
        }
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete sales orders.');
        }

        DB::beginTransaction();
        try {
            // If order was not already cancelled/returned, refund stock
            if (!in_array($salesOrder->status, ['cancelled', 'returned'])) {
                $this->refundStock($salesOrder);
            }

            $this->logAudit('deleted', 'SalesOrder', $salesOrder->id, $salesOrder->toArray(), null);

            $salesOrder->delete();

            DB::commit();
            return redirect()->route('sales-orders.index')->with('success', 'Sales order deleted. Stock has been restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────

    /**
     * Handle stock transitions when order status changes.
     * Cancelled/Returned → refund stock back to products.
     */
    private function handleStatusTransition(SalesOrder $salesOrder, string $oldStatus, string $newStatus): void
    {
        $refundStatuses = ['cancelled', 'returned'];
        $wasRefunded = in_array($oldStatus, $refundStatuses);
        $shouldRefund = in_array($newStatus, $refundStatuses);

        // If moving TO cancelled/returned (and wasn't already), refund stock
        if ($shouldRefund && !$wasRefunded) {
            $this->refundStock($salesOrder);
        }

        // If moving FROM cancelled/returned back to active, re-deduct stock
        if ($wasRefunded && !$shouldRefund) {
            $this->deductStock($salesOrder);
        }
    }

    /**
     * Refund stock back to products for all items in the order.
     */
    private function refundStock(SalesOrder $salesOrder): void
    {
        foreach ($salesOrder->items as $item) {
            $product = $item->product;
            $product->quantity += $item->quantity;
            $product->save();

            StockTransaction::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'add',
                'quantity' => $item->quantity,
                'balance_after' => $product->quantity,
                'reference_number' => $salesOrder->order_number,
                'notes' => 'Stock returned — SO ' . $salesOrder->order_number . ' ' . $salesOrder->status,
            ]);
        }
    }

    /**
     * Deduct stock from products (used when reactivating a cancelled order).
     */
    private function deductStock(SalesOrder $salesOrder): void
    {
        foreach ($salesOrder->items as $item) {
            $product = $item->product;
            if ($product->quantity < $item->quantity) {
                throw new \Exception("Insufficient stock for \"{$product->name}\" — available: {$product->quantity}, needed: {$item->quantity}");
            }
            $product->quantity -= $item->quantity;
            $product->save();

            StockTransaction::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'remove',
                'quantity' => $item->quantity,
                'balance_after' => $product->quantity,
                'reference_number' => $salesOrder->order_number,
                'notes' => 'Stock re-deducted — SO ' . $salesOrder->order_number . ' reactivated',
            ]);
        }
    }

    /**
     * Generate a unique order number with collision check.
     */
    private function generateUniqueOrderNumber(): string
    {
        do {
            $number = 'SO-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (SalesOrder::where('order_number', $number)->exists());

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
