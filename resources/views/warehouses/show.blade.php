@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <h2 class="fw-bold mb-0">{{ $warehouse->name }}</h2>
                <p class="text-muted small mb-0">Code: {{ $warehouse->code }}</p>
            </div>
            <div class="ms-auto">
                @if(Auth::user()->canEditEverything())
                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-2"></i> Edit
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Warehouse Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Address</small>
                    <strong>{{ $warehouse->address }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">City</small>
                    <strong>{{ $warehouse->city }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Country</small>
                    <strong>{{ $warehouse->country }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Phone</small>
                    <strong>{{ $warehouse->phone }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
                    @if($warehouse->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Stock at this Warehouse</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouse->productStocks as $stock)
                            <tr>
                                <td class="fw-bold">{{ $stock->product->name }}</td>
                                <td><code>{{ $stock->product->sku }}</code></td>
                                <td>{{ $stock->quantity }} {{ $stock->product->unit }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No stock at this warehouse</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
