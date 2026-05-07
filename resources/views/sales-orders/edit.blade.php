@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h4 class="mb-0">Edit Sales Order - {{ $salesOrder->order_number }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sales-orders.update', $salesOrder) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Customer</label>
                            <select name="customer_id" class="form-select" required>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $salesOrder->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Warehouse</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $salesOrder->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Order Date</label>
                            <input type="date" name="order_date" class="form-control" value="{{ $salesOrder->order_date->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['draft', 'pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'] as $status)
                                    <option value="{{ $status }}" {{ $salesOrder->status == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                @foreach(['unpaid', 'partial', 'paid'] as $pstatus)
                                    <option value="{{ $pstatus }}" {{ $salesOrder->payment_status == $pstatus ? 'selected' : '' }}>{{ ucfirst($pstatus) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Payment Method</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ $salesOrder->payment_method }}" placeholder="e.g. Bank Transfer, Cash">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Expected Delivery</label>
                            <input type="date" name="expected_delivery_date" class="form-control" value="{{ $salesOrder->expected_delivery_date ? $salesOrder->expected_delivery_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $salesOrder->notes }}</textarea>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5">Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
