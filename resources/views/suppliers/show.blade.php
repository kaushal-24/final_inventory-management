@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-0">{{ $supplier->name }}</h2>
        <p class="text-muted">Supplier Profile | <i class="bi bi-geo-alt"></i> {{ $supplier->address ?: 'No address' }}</p>
    </div>
    <div class="col-md-4 text-end">
        @if(Auth::user()->canEditEverything())
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-2"></i> Edit Supplier
        </a>
        @endif
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to List
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Supplier Info -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Contact Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Phone Number</label>
                    <div class="fw-bold text-white fs-5">
                        <i class="bi bi-telephone text-primary me-2"></i> {{ $supplier->phone ?: 'N/A' }}
                    </div>
                </div>
                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Address</label>
                    <div class="text-muted">
                        <i class="bi bi-building text-primary me-2"></i> {{ $supplier->address ?: 'No address recorded' }}
                    </div>
                </div>
                <hr class="opacity-10">
                <div class="row text-center g-0">
                    <div class="col-6 border-end border-secondary">
                        <h4 class="mb-0 fw-bold">{{ $totalProducts }}</h4>
                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Total Items</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 fw-bold text-info">₹{{ number_format($totalValue, 0) }}</h4>
                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Stock Value</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Provided Products -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Product Catalog from this Supplier</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Unit Price</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplier->products as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $product->category->name }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $product->isLowStock() ? 'text-danger' : '' }}">
                                        {{ $product->quantity }} {{ $product->unit }}
                                    </span>
                                </td>
                                <td>₹{{ number_format($product->price, 2) }}</td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No products associated with this supplier.
                                </td>
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
