@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">Suppliers</h2>
        <p class="text-muted small">Manage your supply chain partners</p>
    </div>
    <div class="col-md-6 text-end">
        @if(Auth::user()->canEditEverything())
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i> New Supplier
        </a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent py-3">
        <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, email, phone, company..." value="{{ request('search') }}">
                </div>
            </div>
            <!-- <div class="col-md-3"> -->
                <!-- <select name="is_active" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('is_active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('is_active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select> -->
            <!-- </div> -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100 text-white">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($suppliers as $supplier)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                        <i class="bi bi-building fs-3"></i>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('suppliers.show', $supplier) }}"><i class="bi bi-eye me-2"></i> View Profile</a></li>
                            @if(Auth::user()->canEditEverything())
                            <li><a class="dropdown-item" href="{{ route('suppliers.edit', $supplier) }}"><i class="bi bi-pencil me-2"></i> Edit</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Archive this supplier?')"><i class="bi bi-trash me-2"></i> Archive</button>
                                </form>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <h5 class="fw-bold mb-1">{{ $supplier->name }}</h5>
                <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i> {{ $supplier->address ?: 'No address' }}</p>
                <div class="d-flex align-items-center text-info small mb-3">
                    <i class="bi bi-telephone me-2"></i> {{ $supplier->phone ?: 'N/A' }}
                </div>
                <hr class="opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Associated Items:</small>
                    <span class="badge bg-light text-dark">{{ $supplier->products_count }} Products</span>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm py-5 text-center text-muted">
            <i class="bi bi-truck fs-1 d-block mb-3"></i>
            No suppliers registered in the system.
        </div>
    </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $suppliers->appends(request()->all())->links('pagination::bootstrap-5') }}
</div>
@endsection
