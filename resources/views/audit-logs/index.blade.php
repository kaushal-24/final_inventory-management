@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">Audit Logs</h2>
        <p class="text-muted small">Track all system activities and changes</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="model" class="form-select">
                    <option value="">All Models</option>
                    <option value="Product" {{ request('model') == 'Product' ? 'selected' : '' }}>Product</option>
                    <option value="Category" {{ request('model') == 'Category' ? 'selected' : '' }}>Category</option>
                    <option value="Supplier" {{ request('model') == 'Supplier' ? 'selected' : '' }}>Supplier</option>
                    <option value="User" {{ request('model') == 'User' ? 'selected' : '' }}>User</option>
                    <option value="PurchaseOrder" {{ request('model') == 'PurchaseOrder' ? 'selected' : '' }}>Purchase Order</option>
                    <option value="Batch" {{ request('model') == 'Batch' ? 'selected' : '' }}>Batch</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light bg-opacity-10">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Record ID</th>
                        <th class="pe-4">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                    <tr>
                        <td class="ps-4">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                        <td>
                            @php
                                $actionColors = [
                                    'created' => 'bg-success',
                                    'updated' => 'bg-primary',
                                    'deleted' => 'bg-danger'
                                ];
                            @endphp
                            <span class="badge {{ $actionColors[$log->action] ?? 'bg-secondary' }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td>{{ $log->model }}</td>
                        <td>{{ $log->model_id }}</td>
                        <td class="pe-4">
                            <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-1 d-block mb-3"></i>
                            No audit logs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $auditLogs->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
