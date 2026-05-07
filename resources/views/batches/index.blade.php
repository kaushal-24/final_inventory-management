@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">Batches & Expiry</h2>
        <p class="text-muted small">Track product batches and expiration dates</p>
    </div>
    <div class="col-md-6 text-end">
        @if(Auth::user()->canManageStock())
        <a href="{{ route('batches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i> New Batch
        </a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3">
        <form action="{{ route('batches.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by batch/lot number or product..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="product_id" class="form-select">
                    <option value="">All Products</option>
                    @foreach(\App\Models\Product::all() as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
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
                        <th class="ps-4">Batch Number</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $batch->batch_number }}</td>
                        <td>{{ $batch->product->name }}</td>
                        <td>{{ $batch->warehouse ? $batch->warehouse->name : 'N/A' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold">{{ $batch->quantity_available }} / {{ $batch->quantity }}</span>
                            </div>
                        </td>
                        <td>
                            @if($batch->expiry_date)
                                @php
                                    $daysUntilExpiry = $batch->expiry_date->diffInDays(now(), false);
                                @endphp
                                {{ $batch->expiry_date->format('d M Y') }}
                                @if($daysUntilExpiry <= 30 && $daysUntilExpiry > 0)
                                    <span class="badge bg-warning text-dark ms-1">Expiring Soon</span>
                                @elseif($daysUntilExpiry <= 0)
                                    <span class="badge bg-danger ms-1">Expired</span>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($batch->expiry_date && $batch->expiry_date < now())
                                <span class="badge bg-danger">Expired</span>
                            @elseif($batch->quantity_available == 0)
                                <span class="badge bg-secondary">Depleted</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('batches.show', $batch) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(Auth::user()->canEditEverything())
                                <a href="{{ route('batches.edit', $batch) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('batches.destroy', $batch) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this batch?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                            No batches found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $batches->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
