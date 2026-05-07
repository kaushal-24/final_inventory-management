@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h4 class="mb-0">Edit Batch - {{ $batch->batch_number }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('batches.update', $batch) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product</label>
                            <select name="product_id" class="form-select" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $batch->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Warehouse</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $batch->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Batch Number</label>
                            <input type="text" name="batch_number" class="form-control" value="{{ $batch->batch_number }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lot Number</label>
                            <input type="text" name="lot_number" class="form-control" value="{{ $batch->lot_number }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Manufactured Date</label>
                            <input type="date" name="manufactured_date" class="form-control" value="{{ $batch->manufactured_date ? $batch->manufactured_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" value="{{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Quantity</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="{{ $batch->quantity }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Available Quantity</label>
                            <input type="number" name="quantity_available" class="form-control" min="0" max="{{ $batch->quantity }}" value="{{ $batch->quantity_available }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unit Cost</label>
                            <input type="number" step="0.01" name="unit_cost" class="form-control" min="0" value="{{ $batch->unit_cost }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $batch->notes }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('batches.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5">Update Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
