@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h4 class="mb-0">Edit Customer - {{ $customer->name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Customer Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Company</label>
                            <input type="text" name="company" class="form-control" value="{{ $customer->company }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $customer->email }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ $customer->address }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">City</label>
                            <input type="text" name="city" class="form-control" value="{{ $customer->city }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">State</label>
                            <input type="text" name="state" class="form-control" value="{{ $customer->state }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ $customer->postal_code }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ $customer->country }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tax ID</label>
                            <input type="text" name="tax_id" class="form-control" value="{{ $customer->tax_id }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Credit Limit</label>
                            <input type="number" step="0.01" name="credit_limit" class="form-control" min="0" value="{{ $customer->credit_limit }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ $customer->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$customer->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $customer->notes }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
