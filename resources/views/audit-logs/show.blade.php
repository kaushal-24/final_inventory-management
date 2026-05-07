@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <h2 class="fw-bold mb-0">Audit Log Details</h2>
                <p class="text-muted small mb-0">ID: {{ $auditLog->id }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <small class="text-muted d-block">Timestamp</small>
                <strong>{{ $auditLog->created_at->format('d M Y H:i:s') }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">User</small>
                <strong>{{ $auditLog->user ? $auditLog->user->name : 'System' }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Action</small>
                @php
                    $actionColors = [
                        'created' => 'bg-success',
                        'updated' => 'bg-primary',
                        'deleted' => 'bg-danger'
                    ];
                @endphp
                <span class="badge {{ $actionColors[$auditLog->action] ?? 'bg-secondary' }}">
                    {{ ucfirst($auditLog->action) }}
                </span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">IP Address</small>
                <strong>{{ $auditLog->ip_address ?? 'N/A' }}</strong>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <small class="text-muted d-block">Model</small>
                <strong>{{ $auditLog->model }}</strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Record ID</small>
                <strong>{{ $auditLog->model_id }}</strong>
            </div>
        </div>

        @if($auditLog->old_values || $auditLog->new_values)
            <div class="row g-4">
                @if($auditLog->old_values)
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3"><i class="bi bi-arrow-left-circle text-muted me-1"></i> Old Values</h6>
                    <div class="card bg-dark overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="table-layout:fixed;">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-3" style="width:35%;">Field</th>
                                        <th class="pe-3" style="width:65%;">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLog->old_values as $key => $value)
                                    <tr>
                                        <td class="ps-3 text-muted small fw-bold text-nowrap">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                        <td class="pe-3 small" style="word-break:break-all; overflow-wrap:break-word;">
                                            @if(is_null($value))
                                                <span class="text-muted fst-italic">null</span>
                                            @elseif(is_array($value))
                                                <span class="text-muted">{{ Str::limit(json_encode($value), 80) }}</span>
                                            @else
                                                {{ Str::limit((string) $value, 100) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
                @if($auditLog->new_values)
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3"><i class="bi bi-arrow-right-circle text-success me-1"></i> New Values</h6>
                    <div class="card bg-dark overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="table-layout:fixed;">
                                <thead class="bg-light bg-opacity-10">
                                    <tr>
                                        <th class="ps-3" style="width:35%;">Field</th>
                                        <th class="pe-3" style="width:65%;">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLog->new_values as $key => $value)
                                    <tr>
                                        <td class="ps-3 text-muted small fw-bold text-nowrap">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                        <td class="pe-3 small" style="word-break:break-all; overflow-wrap:break-word;">
                                            @if(is_null($value))
                                                <span class="text-muted fst-italic">null</span>
                                            @elseif(is_array($value))
                                                <span class="text-muted">{{ Str::limit(json_encode($value), 80) }}</span>
                                            @else
                                                {{ Str::limit((string) $value, 100) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
