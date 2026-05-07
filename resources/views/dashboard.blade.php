@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold text-white">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="text-muted">Here's what's happening with your inventory today.</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex justify-content-end gap-2">
            <div class="bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary text-start">
                <h6 class="text-muted small text-uppercase mb-1">Date</h6>
                <div class="fw-bold text-white"><i class="bi bi-calendar3 me-2"></i>{{ date('M d, Y') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Total Products -->
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Total Products</h6>
                        <h3 class="mb-0">{{ $totalProducts }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                        <i class="bi bi-box fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Value -->
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Inventory Value</h6>
                        <h3 class="mb-0">₹{{ number_format($totalInventoryValue, 2) }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Low Stock Items</h6>
                        <h3 class="mb-0 text-danger">{{ $lowStockCount }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suppliers -->
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Active Suppliers</h6>
                        <h3 class="mb-0">{{ $totalSuppliers }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Stats Row -->
<div class="row g-4 mt-1">
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Pending Sales</h6>
                        <h3 class="mb-0 text-warning">{{ $pendingSalesOrders }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-secondary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Pending POs</h6>
                        <h3 class="mb-0">{{ $pendingPurchaseOrders }}</h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-3">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Total Sales</h6>
                        <h3 class="mb-0 text-success">₹{{ number_format($totalSalesValue, 0) }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Total Purchases</h6>
                        <h3 class="mb-0 text-primary">₹{{ number_format($totalPurchaseValue, 0) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                        <i class="bi bi-graph-down-arrow fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5 g-4">
    <!-- Quick Actions -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    @if(Auth::user()->canEditEverything())
                    <a href="{{ route('products.create') }}" class="btn btn-outline-primary py-2 px-4">
                        <i class="bi bi-plus-lg me-2"></i> Add Product
                    </a>
                    <a href="{{ route('sales-orders.create') }}" class="btn btn-outline-success py-2 px-4">
                        <i class="bi bi-cart-plus me-2"></i> New Sales Order
                    </a>
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-outline-warning py-2 px-4">
                        <i class="bi bi-receipt me-2"></i> New Purchase Order
                    </a>
                    <a href="{{ route('categories.create') }}" class="btn btn-outline-info py-2 px-4">
                        <i class="bi bi-grid me-2"></i> New Category
                    </a>
                    @endif
                    <a href="{{ route('reports.export') }}" class="btn btn-outline-secondary py-2 px-4">
                        <i class="bi bi-file-earmark-excel me-2"></i> Export Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 g-4">
    <!-- Recent Stock Movements -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3">
                <h5 class="mb-0">Recent Stock Movements</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Balance</th>
                                <th>Reference</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $transaction->product->name }}</div>
                                    <small class="text-muted">{{ $transaction->product->sku }}</small>
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
                                <td><small class="text-muted">{{ $transaction->reference_number ?? '-' }}</small></td>
                                <td>{{ $transaction->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No recent stock movements.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Moving Products -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Top Moving Products</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>Movements</th>
                                <th class="pe-4 text-end">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topMovingProducts as $topProduct)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $topProduct->name }}</div>
                                    <small class="text-muted">{{ $topProduct->sku }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill px-3">{{ $topProduct->transactions_count }}</span>
                                </td>
                                <td class="pe-4 text-end fw-bold {{ $topProduct->isLowStock() ? 'text-danger' : '' }}">
                                    {{ $topProduct->quantity }} {{ $topProduct->unit }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right sidebar -->
    <div class="col-md-4">
        <!-- Alerts Card -->
        @if($expiringSoonCount > 0 || $lowStockCount > 0)
        <div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0"><i class="bi bi-bell text-danger me-2"></i>Alerts</h5>
            </div>
            <div class="card-body">
                @if($lowStockCount > 0)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-2">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-danger">{{ $lowStockCount }} Low Stock Items</div>
                        <small class="text-muted">Products below minimum level</small>
                    </div>
                </div>
                @endif
                @if($expiringSoonCount > 0)
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-2">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-warning">{{ $expiringSoonCount }} Batches Expiring</div>
                        <small class="text-muted">Within the next 30 days</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Low Stock List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3">
                <h5 class="mb-0">Low Stock Warning</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @php
                        $lowStockItems = \App\Models\Product::whereColumn('quantity', '<=', 'min_stock_level')->limit(5)->get();
                    @endphp
                    @forelse($lowStockItems as $item)
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold">{{ $item->name }}</div>
                            <small class="text-muted">SKU: {{ $item->sku }}</small>
                        </div>
                        <span class="badge bg-danger rounded-pill">{{ $item->quantity }} {{ $item->unit }}</span>
                    </li>
                    @empty
                    <li class="list-group-item bg-transparent text-center py-4 text-muted">
                        <i class="bi bi-check2-circle text-success fs-2 d-block mb-2"></i>
                        All items are sufficiently stocked.
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Category Distribution</h5>
            </div>
            <div class="card-body">
                @foreach($categoryDistribution as $dist)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span>{{ $dist['name'] }}</span>
                            <span class="text-muted">{{ $dist['count'] }} Products ({{ round($dist['percentage']) }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $dist['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
