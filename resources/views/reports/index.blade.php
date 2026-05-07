@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">Inventory Report</h2>
        <p class="text-muted small">Comprehensive stock analysis and valuation</p>
    </div>
    <div class="col-md-6 text-end no-print">
        <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-success me-2">
            <i class="bi bi-file-earmark-excel me-2"></i> Export CSV
        </a>
        <button class="btn btn-outline-info" onclick="window.print()">
            <i class="bi bi-printer me-2"></i> Print Report
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-header bg-transparent py-3">
        <h5 class="mb-0"><i class="bi bi-filter me-2"></i>Filter Report</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or SKU" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Stock Status</label>
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="in" {{ request('stock_status') == 'in' ? 'selected' : '' }}>In Stock</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Price Range (₹)</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="min_price" class="form-control" placeholder="Min" value="{{ request('min_price') }}">
                    <input type="number" name="max_price" class="form-control" placeholder="Max" value="{{ request('max_price') }}">
                </div>
            </div>
            <div class="col-md-12 text-end">
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary me-2">Clear</a>
                <button type="submit" class="btn btn-sm btn-primary px-4">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-4">
                <h6 class="text-primary text-uppercase small fw-bold">Total Stock Valuation</h6>
                <h2 class="mb-0 text-white">₹{{ number_format($products->sum(fn($p) => $p->price * $p->quantity), 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-4">
                <h6 class="text-info text-uppercase small fw-bold">Total Units in Stock</h6>
                <h2 class="mb-0 text-white">{{ $products->sum('quantity') }} Units</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-4">
                <h6 class="text-warning text-uppercase small fw-bold">Low Stock Items</h6>
                <h2 class="mb-0 text-white">{{ $products->filter(fn($p) => $p->isLowStock())->count() }} Items</h2>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light bg-opacity-10">
                    <tr>
                        <th class="ps-4">Product Details</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Unit Price</th>
                        <th>On Hand</th>
                        <th>Total Value</th>
                        <th class="pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="{{ $product->isLowStock() ? 'bg-danger bg-opacity-10' : '' }}">
                        <td class="ps-4">
                            <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
                                <div class="fw-bold text-white">{{ $product->name }}</div>
                                <small class="text-muted">SKU: <code>{{ $product->sku }}</code></small>
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('categories.show', $product->category) }}" class="text-decoration-none">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $product->category->name }}</span>
                            </a>
                        </td>
                        <td>
                            @if($product->supplier)
                                <a href="{{ route('suppliers.show', $product->supplier) }}" class="text-decoration-none text-white">
                                    {{ $product->supplier->name }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->quantity }} {{ $product->unit }}</td>
                        <td class="fw-bold">₹{{ number_format($product->price * $product->quantity, 2) }}</td>
                        <td class="pe-4">
                            @if($product->quantity == 0)
                                <span class="badge bg-danger">OUT</span>
                            @elseif($product->isLowStock())
                                <span class="badge bg-warning text-dark">LOW</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">No report data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
