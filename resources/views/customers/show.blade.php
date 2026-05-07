@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <h2 class="fw-bold mb-0">{{ $customer->name }}</h2>
                <p class="text-muted small mb-0">{{ $customer->company }}</p>
            </div>
            <div class="ms-auto">
                @if(Auth::user()->canEditEverything())
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
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
                <h5 class="mb-0">Customer Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Email</small>
                    <strong>{{ $customer->email ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Phone</small>
                    <strong>{{ $customer->phone ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Address</small>
                    <p>{{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }} {{ $customer->postal_code }}, {{ $customer->country }}</p>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Tax ID</small>
                    <strong>{{ $customer->tax_id ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Credit Limit</small>
                    <strong>{{ number_format($customer->credit_limit, 2) }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Balance</small>
                    <strong class="{{ $customer->balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($customer->balance, 2) }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
                    @if($customer->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                @if($customer->notes)
                <div class="mb-3">
                    <small class="text-muted d-block">Notes</small>
                    <p>{{ $customer->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Sales Orders</h5>
                <a href="{{ route('sales-orders.create') }}?customer={{ $customer->id }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> New Order
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Pay Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->salesOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('sales-orders.show', $order) }}" class="text-decoration-none text-white fw-bold">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->order_date->format('d M Y') }}</td>
                                <td class="fw-bold">{{ $order->currency }} {{ number_format($order->total, 2) }}</td>
                                <td>
                                    @if($order->payment_method)
                                        <span class="text-muted">{{ $order->payment_method }}</span>
                                    @else
                                        <span class="text-muted fst-italic">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-secondary',
                                            'pending' => 'bg-warning text-dark',
                                            'processing' => 'bg-primary',
                                            'shipped' => 'bg-info text-dark',
                                            'delivered' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'returned' => 'bg-warning'
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$order->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No sales orders for this customer</td>
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
