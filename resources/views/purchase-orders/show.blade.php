@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <h2 class="fw-bold mb-0">{{ $purchaseOrder->po_number }}</h2>
                <p class="text-muted small mb-0">Created by {{ $purchaseOrder->user->name }}</p>
            </div>
            <div class="ms-auto">
                @if(Auth::user()->canEditEverything())
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        Update Status
                    </button>
                    <ul class="dropdown-menu">
                        @foreach(['draft', 'pending', 'approved', 'partially_received', 'received', 'cancelled'] as $status)
                        <li>
                            <form action="{{ route('purchase-orders.status', $purchaseOrder) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button type="submit" class="dropdown-item">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">PO Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Supplier</small>
                    <strong>{{ $purchaseOrder->supplier->name }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Warehouse</small>
                    <strong>{{ $purchaseOrder->warehouse ? $purchaseOrder->warehouse->name : 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Order Date</small>
                    <strong>{{ $purchaseOrder->order_date->format('d M Y') }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Expected Delivery</small>
                    <strong>{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
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
                    <div class="d-flex gap-2">
                        <span class="badge {{ $statusColors[$purchaseOrder->status] ?? 'bg-secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                        </span>
                        @if($purchaseOrder->payment_status)
                        <span class="badge {{ $paymentStatusColors[$purchaseOrder->payment_status] ?? 'bg-secondary' }}">
                            {{ ucfirst($purchaseOrder->payment_status) }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span>{{ $purchaseOrder->currency }} {{ number_format($purchaseOrder->subtotal, 2) }}</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Tax</span>
                        <span>{{ $purchaseOrder->currency }} {{ number_format($purchaseOrder->tax, 2) }}</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Shipping</span>
                        <span>{{ $purchaseOrder->currency }} {{ number_format($purchaseOrder->shipping, 2) }}</span>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold text-info mb-2">
                    <span>Total</span>
                    <span>{{ $purchaseOrder->currency }} {{ number_format($purchaseOrder->total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold text-muted small">
                    <span>Pay Method</span>
                    <span>{{ $purchaseOrder->payment_method ?: 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>SKU</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $item->product->name }}</td>
                                <td><code>{{ $item->product->sku }}</code></td>
                                <td>{{ $item->quantity_ordered }}</td>
                                <td>{{ $purchaseOrder->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                <td class="fw-bold">{{ $purchaseOrder->currency }} {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($purchaseOrder->notes)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                {{ $purchaseOrder->notes }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
