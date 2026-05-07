@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <h2 class="fw-bold mb-0">{{ $salesOrder->order_number }}</h2>
                <p class="text-muted small mb-0">Created by {{ $salesOrder->user->name }}</p>
            </div>
            <div class="ms-auto">
                @if(Auth::user()->canEditEverything())
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        Update Status
                    </button>
                    <ul class="dropdown-menu">
                        @foreach(['draft', 'pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'] as $status)
                        <li>
                            <form action="{{ route('sales-orders.status', $salesOrder) }}" method="POST">
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
                <h5 class="mb-0">Order Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Customer</small>
                    <strong>{{ $salesOrder->customer->name }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Warehouse</small>
                    <strong>{{ $salesOrder->warehouse ? $salesOrder->warehouse->name : 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Order Date</small>
                    <strong>{{ $salesOrder->order_date->format('d M Y') }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Expected Delivery</small>
                    <strong>{{ $salesOrder->expected_delivery_date ? $salesOrder->expected_delivery_date->format('d M Y') : 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
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
                    <span class="badge {{ $statusColors[$salesOrder->status] ?? 'bg-secondary' }}">
                        {{ ucfirst(str_replace('_', ' ', $salesOrder->status)) }}
                    </span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Payment Status</small>
                    <div class="d-flex flex-column gap-1">
                        @if($salesOrder->payment_status == 'paid')
                            <span class="badge bg-success" style="width: fit-content;">Paid</span>
                        @elseif($salesOrder->payment_status == 'partial')
                            <span class="badge bg-warning text-dark" style="width: fit-content;">Partial</span>
                        @else
                            <span class="badge bg-danger" style="width: fit-content;">Unpaid</span>
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
                        <span>{{ $salesOrder->currency }} {{ number_format($salesOrder->subtotal, 2) }}</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Tax</span>
                        <span>{{ $salesOrder->currency }} {{ number_format($salesOrder->tax, 2) }}</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Shipping</span>
                        <span>{{ $salesOrder->currency }} {{ number_format($salesOrder->shipping, 2) }}</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Discount</span>
                        <span>-{{ $salesOrder->currency }} {{ number_format($salesOrder->discount, 2) }}</span>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold text-info mb-2">
                    <span>Total</span>
                    <span>{{ $salesOrder->currency }} {{ number_format($salesOrder->total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold text-muted small">
                    <span>Pay Method</span>
                    <span>{{ $salesOrder->payment_method ?: 'N/A' }}</span>
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
                                <th>Discount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesOrder->items as $item)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $item->product->name }}</td>
                                <td><code>{{ $item->product->sku }}</code></td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $salesOrder->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ $item->discount > 0 ? $salesOrder->currency . ' ' . number_format($item->discount, 2) : '-' }}</td>
                                <td class="fw-bold">{{ $salesOrder->currency }} {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($salesOrder->notes)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                {{ $salesOrder->notes }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
