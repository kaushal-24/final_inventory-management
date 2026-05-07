@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">Products</h2>
        <p class="text-muted small">Manage your industrial parts and materials</p>
    </div>
    <div class="col-md-6 text-end">
        @if(Auth::user()->canEditEverything())
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i> New Product
        </a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3">
        <form action="{{ route('products.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by SKU or Name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Category::all() as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">All Stock Levels</option>
                    <option value="in" {{ request('stock_status') == 'in' ? 'selected' : '' }}>In Stock</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100 text-white">Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light bg-opacity-10">
                    <tr>
                        <th class="ps-4">Product Info</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Stock Level</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary bg-opacity-20 rounded d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-box text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold">
                                        {{ $product->name }}
                                        @if($product->created_at->gt(now()->subDay()))
                                            <span class="badge bg-primary ms-1 small" style="font-size: 0.6rem;">NEW</span>
                                        @endif
                                        @if($product->updated_at->gt(now()->subDay()) && !$product->created_at->gt(now()->subDay()))
                                            <span class="badge bg-info text-dark ms-1 small" style="font-size: 0.6rem;">UPDATED</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">SKU: <code>{{ $product->sku }}</code></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('categories.show', $product->category) }}" class="text-decoration-none">
                                <span class="badge bg-secondary-subtle text-secondary px-3">{{ $product->category->name }}</span>
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
                        <td class="fw-bold text-info">₹{{ number_format($product->price, 2) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold">{{ $product->quantity }} {{ $product->unit }}</span>
                                @if($product->quantity == 0)
                                    <span class="badge bg-danger rounded-pill">OUT OF STOCK</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark rounded-pill">LOW STOCK</span>
                                @else
                                    <span class="badge bg-success rounded-pill">IN STOCK</span>
                                @endif
                            </div>
                            <div class="progress mt-1" style="height: 4px; width: 100px;">
                                @php 
                                    $denominator = ($product->min_stock_level > 0 ? $product->min_stock_level : 1) * 5;
                                    $percent = min(100, ($product->quantity / $denominator) * 100); 
                                @endphp
                                <div class="progress-bar {{ $product->isLowStock() ? 'bg-danger' : 'bg-success' }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(Auth::user()->canEditEverything())
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Archive this product?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-1 d-block mb-3"></i>
                            No products matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $products->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
