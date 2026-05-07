@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h4 class="mb-0">Create Sales Order</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sales-orders.store') }}" method="POST" id="soForm">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Customer</label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', request('customer')) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Warehouse</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Order Date</label>
                            <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', date('Y-m-d')) }}" required>
                            @error('order_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3">Order Items</h5>
                    <div id="itemsContainer">
                        <div class="item-row card mb-3 p-3">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Product</label>
                                    <select name="items[0][product_id]" class="form-select product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->quantity }}">{{ $product->name }} ({{ $product->sku }}) - Stock: {{ $product->quantity }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Quantity</label>
                                    <input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Unit Price</label>
                                    <input type="number" step="0.01" name="items[0][unit_price]" class="form-control item-price" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Discount</label>
                                    <input type="number" step="0.01" name="items[0][discount]" class="form-control item-discount" min="0" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Total</label>
                                    <input type="text" class="form-control item-total" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger remove-item w-100">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="addItem">
                            <i class="bi bi-plus-lg me-2"></i> Add Item
                        </button>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Subtotal</label>
                            <input type="text" id="subtotal" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tax (%)</label>
                            <input type="number" step="0.01" name="tax" class="form-control" id="taxInput" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Shipping</label>
                            <input type="number" step="0.01" name="shipping" class="form-control" id="shippingInput" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Grand Total</label>
                            <input type="text" id="grandTotal" class="form-control fw-bold text-info" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5">Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let itemCount = 1;

function calculateRowTotal(row) {
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
    const total = (qty * price) - discount;
    row.querySelector('.item-total').value = total.toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let subtotal = 0;
    document.querySelectorAll('.item-total').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    
    const tax = parseFloat(document.getElementById('taxInput').value) || 0;
    const shipping = parseFloat(document.getElementById('shippingInput').value) || 0;
    const taxAmount = subtotal * (tax / 100);
    const total = subtotal + taxAmount + shipping;
    document.getElementById('grandTotal').value = total.toFixed(2);
}

document.getElementById('addItem').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const template = container.querySelector('.item-row').cloneNode(true);
    
    template.querySelectorAll('input, select').forEach(input => {
        input.name = input.name.replace(/\[\d+\]/, '[' + itemCount + ']');
        input.value = input.tagName === 'SELECT' ? '' : (input.classList.contains('item-qty') ? '1' : (input.classList.contains('item-discount') ? '0' : ''));
    });
    
    container.appendChild(template);
    itemCount++;
});

document.getElementById('itemsContainer').addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            e.target.closest('.item-row').remove();
            calculateGrandTotal();
        }
    }
});

document.getElementById('itemsContainer').addEventListener('change', function(e) {
    if (e.target.classList.contains('product-select')) {
        const row = e.target.closest('.item-row');
        const price = e.target.options[e.target.selectedIndex].dataset.price;
        if (price) {
            row.querySelector('.item-price').value = price;
            calculateRowTotal(row);
        }
    }
    if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price') || e.target.classList.contains('item-discount')) {
        calculateRowTotal(e.target.closest('.item-row'));
    }
});

document.querySelectorAll('#taxInput, #shippingInput').forEach(input => {
    input.addEventListener('input', calculateGrandTotal);
});
</script>
@endsection
