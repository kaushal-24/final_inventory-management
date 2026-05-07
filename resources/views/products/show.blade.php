@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold mb-0">{{ $product->name }}</h2>
        <p class="text-muted">SKU: <code>{{ $product->sku }}</code> | {{ $product->category->name }}</p>
    </div>
    <div class="col-md-4 text-end">
        @if(Auth::user()->canEditEverything())
        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-2"></i> Edit Product
        </a>
        @endif
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to List
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Product Stats -->
    <div class="col-md-4">
        @if($product->image)
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid" style="width: 100%; height: 300px; object-fit: cover;">
            </div>
        @endif
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Stock Overview</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-4 mb-4 bg-dark bg-opacity-25 rounded-3">
                    <h1 class="display-4 fw-bold {{ $product->isLowStock() ? 'text-danger' : 'text-success' }}">
                        {{ $product->quantity }}
                    </h1>
                    <p class="text-muted text-uppercase small mb-0">{{ $product->unit }} on hand</p>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                        <span class="text-muted">Unit Price:</span>
                        <span class="fw-bold">₹{{ number_format($product->price, 2) }}</span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                        <span class="text-muted">Total Value:</span>
                        <span class="fw-bold text-info">₹{{ number_format($product->price * $product->quantity, 2) }}</span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                        <span class="text-muted">Min. Stock Level:</span>
                        <span class="badge bg-secondary">{{ $product->min_stock_level }} {{ $product->unit }}</span>
                    </li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0">
                        <span class="text-muted">Supplier:</span>
                        <span class="fw-bold">{{ $product->supplier->name ?? 'N/A' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Movement Audit Trail</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $product->transactions->count() }} Records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th class="ps-4">Date & Time</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>New Balance</th>
                                <th>Performed By</th>
                                <th class="pe-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->transactions->sortByDesc('created_at') as $transaction)
                            <tr>
                                <td class="ps-4 small text-muted">
                                    {{ $transaction->created_at->format('M d, Y') }}<br>
                                    {{ $transaction->created_at->format('H:i A') }}
                                </td>
                                <td>
                                    @if($transaction->type == 'add')
                                        <span class="badge bg-success-subtle text-success px-3">STOCK IN</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3">STOCK OUT</span>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $transaction->type == 'add' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type == 'add' ? '+' : '-' }}{{ $transaction->quantity }}
                                </td>
                                <td>{{ $transaction->balance_after }}</td>
                                <td>{{ $transaction->user->name }}</td>
                                <td>{{ $transaction->notes ?? '-' }}</td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-1 d-block mb-3"></i>
                                    No movements recorded for this product.
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
