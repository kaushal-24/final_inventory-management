@extends('layouts.app')

@section('content')
<div class="col-md-4">
    <div class="text-center mb-4">
        <h2 class="text-white fw-bold"><i class="bi bi-box-seam-fill me-2 text-primary"></i>Inventory</h2>
        <p class="text-muted">Industrial Inventory Management</p>
    </div>
    <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
            <h4 class="fw-bold mb-4 text-center text-white">Sign In</h4>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label text-muted small text-uppercase fw-bold">Email Address</label>
                    <div class="input-group border-bottom border-secondary">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control bg-transparent border-0 text-white @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="admin@example.com">
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label text-muted small text-uppercase fw-bold">Password</label>
                    <div class="input-group border-bottom border-secondary">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control bg-transparent border-0 text-white @error('password') is-invalid @enderror" required placeholder="••••••••">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                    Access Dashboard <i class="bi bi-arrow-right ms-2"></i>
                </button>
                <div class="mt-4 text-center">
                    <p class="text-muted small">New to the platform? <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-bold">Register Account</a></p>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center mt-4 text-muted small">
        &copy; {{ date('Y') }} Inventory Industrial Solutions
    </div>
</div>
@endsection
