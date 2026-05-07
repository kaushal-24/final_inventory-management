@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('batches.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <h2 class="fw-bold mb-0">Batch: {{ $batch->batch_number }}</h2>
                <p class="text-muted small mb-0">{{ $batch->product->name }}</p>
            </div>
            <div class="ms-auto">
                @if(Auth::user()->canEditEverything())
                <a href="{{ route('batches.edit', $batch) }}" class="btn btn-warning">
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
                <h5 class="mb-0">Batch Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Product</small>
                    <strong>{{ $batch->product->name }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Warehouse</small>
                    <strong>{{ $batch->warehouse ? $batch->warehouse->name : 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Lot Number</small>
                    <strong>{{ $batch->lot_number ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Quantity</small>
                    <strong>{{ $batch->quantity_available }} / {{ $batch->quantity }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Manufactured Date</small>
                    <strong>{{ $batch->manufactured_date ? $batch->manufactured_date->format('d M Y') : 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Expiry Date</small>
                    <strong>{{ $batch->expiry_date ? $batch->expiry_date->format('d M Y') : 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Unit Cost</small>
                    <strong>{{ $batch->unit_cost ? number_format($batch->unit_cost, 2) : 'N/A' }}</strong>
                </div>
                @if($batch->notes)
                <div class="mb-3">
                    <small class="text-muted d-block">Notes</small>
                    <p>{{ $batch->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Transaction History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-10">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batch->stockTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="badge {{ $transaction->type == 'add' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="fw-bold">{{ $transaction->type == 'add' ? '+' : '-' }}{{ $transaction->quantity }}</td>
                                <td>{{ $transaction->notes ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No transactions for this batch</td>
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
