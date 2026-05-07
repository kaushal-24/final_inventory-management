@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-0">{{ $category->name }}</h2>
        <p class="text-muted">
            Category Details 
            @if($category->parent)
                | Parent: <span class="text-info">{{ $category->parent->name }}</span>
            @endif
        </p>
    </div>
    <div class="col-md-4 text-end">
        @if(Auth::user()->canEditEverything())
        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-2"></i> Edit Category
        </a>
        @endif
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to List
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Category Overview -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Overview</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">{{ $category->description ?: 'No description available for this category.' }}</p>
                
                <hr class="opacity-10">
                
                <div class="row text-center g-0">
                    <div class="col-6 border-end border-secondary">
                        <h4 class="mb-0 fw-bold">{{ $products->count() }}</h4>
                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Unique Items</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 fw-bold text-success">{{ $totalStock }}</h4>
                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Total Stock</small>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3">
                        <h6 class="text-primary text-uppercase small fw-bold mb-1">Total Category Value</h6>
                        <h3 class="mb-0 text-white">₹{{ number_format($totalValue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        @if($category->children->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Sub-Categories</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($category->children as $child)
                    <a href="{{ route('categories.show', $child) }}" class="list-group-item list-group-item-action bg-transparent d-flex justify-content-between align-items-center py-3">
                        <span class="text-white">{{ $child->name }}</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <!-- Products List -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Products in this Category (incl. Sub-categories)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Stock</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </td>
                                <td>
                                    @if($product->category_id == $category->id)
                                        <span class="badge bg-primary bg-opacity-10 text-primary">Direct</span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info">{{ $product->category->name }}</span>
                                    @endif
                                </td>
                                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="fw-bold {{ $product->isLowStock() ? 'text-danger' : '' }}">
                                        {{ $product->quantity }} {{ $product->unit }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No products found in this category hierarchy.
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
