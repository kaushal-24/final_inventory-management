@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">Purchase Orders</h2>
        <p class="text-muted small">Manage your purchase orders from suppliers</p>
    </div>
    <div class="col-md-6 text-end">
        @if(Auth::user()->canEditEverything())
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i> New PO
        </a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3">
        <form action="{{ route('purchase-orders.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by PO number or supplier..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                        <th class="ps-4">PO Number</th>
                        <th>Supplier</th>
                        <th>Warehouse</th>
                        <th>Order Date</th>
                        <th>Total</th>
                        <th>Pay Method</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $po->po_number }}</td>
                        <td>{{ $po->supplier->name }}</td>
                        <td>{{ $po->warehouse ? $po->warehouse->name : 'N/A' }}</td>
                        <td>{{ $po->order_date->format('d M Y') }}</td>
                        <td class="fw-bold text-info">{{ $po->currency }} {{ number_format($po->total, 2) }}</td>
                        <td>
                            @if($po->payment_method)
                                <span class="text-muted">{{ $po->payment_method }}</span>
                            @else
                                <span class="text-muted fst-italic">N/A</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-primary',
                                    'partially_received' => 'bg-info text-dark',
                                    'received' => 'bg-success',
                                    'cancelled' => 'bg-danger'
                                ];
                                $paymentStatusColors = [
                                    'unpaid' => 'bg-danger',
                                    'partial' => 'bg-warning text-dark',
                                    'paid' => 'bg-success'
                                ];
                            @endphp
                            <div class="d-flex flex-column gap-1">
                                <span class="badge {{ $statusColors[$po->status] ?? 'bg-secondary' }}" style="width: fit-content;">
                                    {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                </span>
                                @if($po->payment_status)
                                <span class="badge {{ $paymentStatusColors[$po->payment_status] ?? 'bg-secondary' }}" style="width: fit-content;">
                                    {{ ucfirst($po->payment_status) }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(Auth::user()->canEditEverything())
                                <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('purchase-orders.destroy', $po) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this PO?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-3"></i>
                            No purchase orders found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $purchaseOrders->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
