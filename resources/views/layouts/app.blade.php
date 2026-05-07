<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Inventory System') }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #3b82f6;
            --dark-bg: #0f172a;
            --sidebar-bg: #1e293b;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            color: #e2e8f0;
        }
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            border-right: 1px solid #334155;
            transition: all 0.3s ease;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }
        .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            border-radius: 8px;
            margin: 0.25rem 0.75rem;
        }
        .nav-link:hover, .nav-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
        }
        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid #334155;
            font-weight: 600;
        }
        .table {
            color: #e2e8f0;
        }
        .table-dark {
            --bs-table-bg: #1e293b;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .badge-low-stock {
            background-color: #ef4444;
            color: white;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #334155;
            margin-bottom: 1rem;
        }
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem;
            border-top: 1px solid #334155;
        }
        /* Auth specific styles */
        .auth-container {
            margin-left: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        @media print {
            .sidebar, .no-print, .btn, .alert, .card-header form, .pagination {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
                background-color: transparent !important;
            }
            .table {
                color: black !important;
            }
            .text-white, .text-muted {
                color: black !important;
            }
        }
    </style>
</head>
<body>
    @auth
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0 text-white"><i class="bi bi-box-seam-fill me-2"></i>Inventory</h4>
        </div>
        <div class="nav flex-column">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-grid"></i> Categories
            </a>
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Suppliers
            </a>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i> Products
                @php
                    $lowStockCount = \App\Models\Product::whereColumn('quantity', '<=', 'min_stock_level')->count();
                @endphp
                @if($lowStockCount > 0)
                    <span class="badge bg-danger ms-auto">{{ $lowStockCount }}</span>
                @endif
            </a>
            <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Customers
            </a>
            <a href="{{ route('sales-orders.index') }}" class="nav-link {{ request()->routeIs('sales-orders.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check"></i> Sales Orders
            </a>
            <a href="{{ route('purchase-orders.index') }}" class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Purchase Orders
            </a>
            <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Warehouses
            </a>
            <a href="{{ route('batches.index') }}" class="nav-link {{ request()->routeIs('batches.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Batches & Expiry
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>
            <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Audit Logs
            </a>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Staff Management
            </a>
            @endif
        </div>
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm rounded-circle bg-primary text-white p-2">
                    <i class="bi bi-person"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="mb-0 small fw-bold text-white text-truncate">
                        <a href="{{ route('profile.edit') }}" class="text-white text-decoration-none">{{ Auth::user()->name }}</a>
                    </p>
                    <small class="text-muted">{{ ucfirst(Auth::user()->role) }}</small>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-link text-danger p-0">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endauth

    <main class="{{ Auth::check() ? 'main-content' : 'auth-container d-flex align-items-center justify-content-center min-vh-100' }}">
        <div class="{{ Auth::check() ? 'container-fluid' : 'container d-flex justify-content-center w-100' }}">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i> Please fix the following errors:
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
