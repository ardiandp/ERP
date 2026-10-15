<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ERP Dian Mustika')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
@auth
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">ERP Dian Mustika</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Organization</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('companies.index') }}">Company</a></li>
                        <li><a class="dropdown-item" href="{{ route('branches.index') }}">Branch</a></li>
                        <li><a class="dropdown-item" href="{{ route('departments.index') }}">Department</a></li>
                        <li><a class="dropdown-item" href="{{ route('positions.index') }}">Position</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Master Data</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('customers.index') }}">Customer</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'product-categories') }}">Product Category</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'brands') }}">Brand</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'units') }}">Unit</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'products') }}">Product</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'service-categories') }}">Service Category</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'services') }}">Service</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'service-packages') }}">Service Package</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'suppliers') }}">Supplier</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'banks') }}">Bank</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'financial-accounts') }}">Financial Account</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'payment-methods') }}">Payment Method</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'asset-categories') }}">Asset Category</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'digital-account-categories') }}">Digital Account Category</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Inventory</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('warehouses.index') }}">Warehouse</a></li>
                        <li><a class="dropdown-item" href="{{ route('inventory.stock') }}">Stock</a></li>
                        <li><a class="dropdown-item" href="{{ route('inventory.movements') }}">Movements</a></li>
                        <li><a class="dropdown-item" href="{{ route('inventory.adjustment.create') }}">Adjustment</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('purchase-orders.index') }}">Purchase Orders</a></li>
                        <li><a class="dropdown-item" href="{{ route('goods-receipts.index') }}">Goods Receipts</a></li>
                        <li><a class="dropdown-item" href="{{ route('stock-transfers.index') }}">Stock Transfers</a></li>
                        <li><a class="dropdown-item" href="{{ route('stock-opnames.index') }}">Stock Opname</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Sales & Booking</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('sales.create') }}">POS / Kasir</a></li>
                        <li><a class="dropdown-item" href="{{ route('sales.index') }}">Sales</a></li>
                        <li><a class="dropdown-item" href="{{ route('bookings.index') }}">Bookings</a></li>
                        <li><a class="dropdown-item" href="{{ route('sales.returns.index') }}">Returns</a></li>
                        <li><a class="dropdown-item" href="{{ route('commissions.index') }}">Commissions</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'commission-rules') }}">Commission Rules</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('employees.index') }}">Employees</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">HR & Payroll</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('shifts.index') }}">Shifts</a></li>
                        <li><a class="dropdown-item" href="{{ route('attendances.index') }}">Attendances</a></li>
                        <li><a class="dropdown-item" href="{{ route('overtimes.index') }}">Overtime</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('salary-structures.index') }}">Salary Structures</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'salary-components') }}">Salary Components</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('payroll-periods.index') }}">Payroll Periods</a></li>
                        <li><a class="dropdown-item" href="{{ route('payrolls.index') }}">Payrolls</a></li>
                        <li><a class="dropdown-item" href="{{ route('payslips.index') }}">Payslips</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Finance</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('finance.accounts') }}">Cash & Bank</a></li>
                        <li><a class="dropdown-item" href="{{ route('bank-transactions.index') }}">Bank Mutations</a></li>
                        <li><a class="dropdown-item" href="{{ route('bank-reconciliations.index') }}">Reconciliations</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('bills.index') }}">Bills</a></li>
                        <li><a class="dropdown-item" href="{{ route('bill-templates.index') }}">Bill Templates</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'expense-categories') }}">Expense Categories</a></li>
                        <li><a class="dropdown-item" href="{{ route('expenses.index') }}">Expenses</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Asset & Document</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('assets.index') }}">Assets</a></li>
                        <li><a class="dropdown-item" href="{{ route('media.index') }}">Media Files</a></li>
                        <li><a class="dropdown-item" href="{{ route('digital-accounts.index') }}">Digital Accounts</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'asset-categories') }}">Asset Categories</a></li>
                        <li><a class="dropdown-item" href="{{ route('masterdata.index', 'digital-account-categories') }}">Digital Account Categories</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('reports.index') }}">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Users</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('roles.index') }}">Roles</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('notifications.index') }}">
                        Notifikasi
                        @php($unread = auth()->user()->unreadNotifications->count())
                        @if ($unread > 0)
                            <span class="badge bg-danger">{{ $unread }}</span>
                        @endif
                    </a>
                </li>
            </ul>
            <form method="POST" action="{{ route('logout') }}" class="d-flex">
                @csrf
                <button class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>
@endauth
<div class="container">
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
