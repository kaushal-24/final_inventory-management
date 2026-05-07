<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::with(['product', 'warehouse']);
        
        if ($request->filled('search')) {
            $query->where('batch_number', 'like', '%' . $request->search . '%')
                  ->orWhere('lot_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('product', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        $batches = $query->latest()->paginate(15);
        return view('batches.index', compact('batches'));
    }

    public function create()
    {
        if (!Auth::user()->canManageStock()) {
            abort(403, 'Unauthorized action.');
        }
        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('batches.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->canManageStock()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'batch_number' => 'required|string|max:100',
            'lot_number' => 'nullable|string|max:100',
            'manufactured_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Batch::create([
            'product_id' => $request->product_id,
            'warehouse_id' => $request->warehouse_id,
            'batch_number' => $request->batch_number,
            'lot_number' => $request->lot_number,
            'manufactured_date' => $request->manufactured_date,
            'expiry_date' => $request->expiry_date,
            'quantity' => $request->quantity,
            'quantity_available' => $request->quantity,
            'unit_cost' => $request->unit_cost,
            'notes' => $request->notes,
        ]);

        return redirect()->route('batches.index')->with('success', 'Batch created successfully.');
    }

    public function show(Batch $batch)
    {
        $batch->load(['product', 'warehouse', 'stockTransactions']);
        return view('batches.show', compact('batch'));
    }

    public function edit(Batch $batch)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('batches.edit', compact('batch', 'products', 'warehouses'));
    }

    public function update(Request $request, Batch $batch)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'batch_number' => 'required|string|max:100|unique:batches,batch_number,' . $batch->id . ',id,product_id,' . $batch->product_id,
            'lot_number' => 'nullable|string|max:100',
            'manufactured_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
            'quantity_available' => 'required|integer|min:0|max:' . $request->quantity,
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $batch->update($request->validated());
        return redirect()->route('batches.index')->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete batches.');
        }
        $batch->delete();
        return redirect()->route('batches.index')->with('success', 'Batch deleted successfully.');
    }
}
