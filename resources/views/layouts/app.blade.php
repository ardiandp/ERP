<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ERP Dian Mustika')</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('theme/matrix-admin/assets/images/favicon.png') }}">
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/matrix-admin/matrix-compat.css') }}" rel="stylesheet">
</head>
<body>
<div class="preloader">
    <div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div>
</div>

<div id="main-wrapper">
    @auth
    <header class="topbar" data-navbarbg="skin5">
        <nav class="navbar top-navbar navbar-expand-md navbar-dark">
            <div class="navbar-header" data-logobg="skin5">
                <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <b class="logo-icon p-l-10">
                        <img src="{{ asset('theme/matrix-admin/assets/images/logo-icon.png') }}" alt="homepage" class="light-logo" />
                    </b>
                    <span class="logo-text"><span class="text-white font-weight-bold">ERP Dian Mustika</span></span>
                </a>
            </div>
            <div class="navbar-collapse collapse" id="navbarSupportedContent">
                <ul class="navbar-nav float-left mr-auto"></ul>
                <ul class="navbar-nav float-right">
                    @php($unread = auth()->user()->unreadNotifications->count())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-bell"></i>
                            @if ($unread > 0)<span class="badge badge-danger">{{ $unread }}</span>@endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated">
                            <a class="dropdown-item" href="{{ route('notifications.index') }}">Notifikasi ({{ $unread }})</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-account-circle"></i> {{ auth()->user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="left-sidebar" data-sidebarbg="skin5">
        <div class="scroll-sidebar">
            <nav class="sidebar-nav">
                <ul id="sidebarnav" class="p-t-30">
                    <li class="sidebar-item">
                        <a class="sidebar-link waves-effect waves-dark {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" aria-expanded="false">
                            <i class="mdi mdi-view-dashboard"></i><span class="hide-menu">Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="mdi mdi-sitemap"></i><span class="hide-menu">Organization</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item"><a href="{{ route('companies.index') }}" class="sidebar-link"><i class="mdi mdi-domain"></i><span class="hide-menu">Company</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('branches.index') }}" class="sidebar-link"><i class="mdi mdi-store"></i><span class="hide-menu">Branch</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('departments.index') }}" class="sidebar-link"><i class="mdi mdi-account-network"></i><span class="hide-menu">Department</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('positions.index') }}" class="sidebar-link"><i class="mdi mdi-badge-account-horizontal"></i><span class="hide-menu">Position</span></a></li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="mdi mdi-database"></i><span class="hide-menu">Master Data</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item"><a href="{{ route('customers.index') }}" class="sidebar-link"><i class="mdi mdi-account-multiple"></i><span class="hide-menu">Customer</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'product-categories') }}" class="sidebar-link"><i class="mdi mdi-tag"></i><span class="hide-menu">Product Category</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'brands') }}" class="sidebar-link"><i class="mdi mdi-bookmark"></i><span class="hide-menu">Brand</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'units') }}" class="sidebar-link"><i class="mdi mdi-scale-balance"></i><span class="hide-menu">Unit</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'products') }}" class="sidebar-link"><i class="mdi mdi-package-variant-closed"></i><span class="hide-menu">Product</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'service-categories') }}" class="sidebar-link"><i class="mdi mdi-tag-multiple"></i><span class="hide-menu">Service Category</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'services') }}" class="sidebar-link"><i class="mdi mdi-spa"></i><span class="hide-menu">Service</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'service-packages') }}" class="sidebar-link"><i class="mdi mdi-gift"></i><span class="hide-menu">Service Package</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'suppliers') }}" class="sidebar-link"><i class="mdi mdi-truck-delivery"></i><span class="hide-menu">Supplier</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'banks') }}" class="sidebar-link"><i class="mdi mdi-bank"></i><span class="hide-menu">Bank</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'financial-accounts') }}" class="sidebar-link"><i class="mdi mdi-wallet"></i><span class="hide-menu">Financial Account</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'payment-methods') }}" class="sidebar-link"><i class="mdi mdi-credit-card"></i><span class="hide-menu">Payment Method</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'asset-categories') }}" class="sidebar-link"><i class="mdi mdi-shape"></i><span class="hide-menu">Asset Category</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'digital-account-categories') }}" class="sidebar-link"><i class="mdi mdi-cellphone-link"></i><span class="hide-menu">Digital Account Category</span></a></li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="mdi mdi-package-variant"></i><span class="hide-menu">Inventory</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item"><a href="{{ route('warehouses.index') }}" class="sidebar-link"><i class="mdi mdi-warehouse"></i><span class="hide-menu">Warehouse</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('inventory.stock') }}" class="sidebar-link"><i class="mdi mdi-cube-outline"></i><span class="hide-menu">Stock</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('inventory.movements') }}" class="sidebar-link"><i class="mdi mdi-swap-horizontal"></i><span class="hide-menu">Movements</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('inventory.adjustment.create') }}" class="sidebar-link"><i class="mdi mdi-tune"></i><span class="hide-menu">Adjustment</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('purchase-orders.index') }}" class="sidebar-link"><i class="mdi mdi-cart"></i><span class="hide-menu">Purchase Orders</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('goods-receipts.index') }}" class="sidebar-link"><i class="mdi mdi-clipboard-check"></i><span class="hide-menu">Goods Receipts</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('stock-transfers.index') }}" class="sidebar-link"><i class="mdi mdi-truck-fast"></i><span class="hide-menu">Stock Transfers</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('stock-opnames.index') }}" class="sidebar-link"><i class="mdi mdi-clipboard-list"></i><span class="hide-menu">Stock Opname</span></a></li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="mdi mdi-cash-multiple"></i><span class="hide-menu">Sales &amp; Booking</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item"><a href="{{ route('sales.create') }}" class="sidebar-link"><i class="mdi mdi-point-of-sale"></i><span class="hide-menu">POS / Kasir</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('sales.index') }}" class="sidebar-link"><i class="mdi mdi-receipt"></i><span class="hide-menu">Sales</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('bookings.index') }}" class="sidebar-link"><i class="mdi mdi-calendar-check"></i><span class="hide-menu">Bookings</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('sales.returns.index') }}" class="sidebar-link"><i class="mdi mdi-backup-restore"></i><span class="hide-menu">Returns</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('commissions.index') }}" class="sidebar-link"><i class="mdi mdi-percent"></i><span class="hide-menu">Commissions</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'commission-rules') }}" class="sidebar-link"><i class="mdi mdi-file-percent"></i><span class="hide-menu">Commission Rules</span></a></li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link waves-effect waves-dark {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}" aria-expanded="false">
                            <i class="mdi mdi-account-multiple-outline"></i><span class="hide-menu">Employees</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="mdi mdi-account-box"></i><span class="hide-menu">HR &amp; Payroll</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item"><a href="{{ route('shifts.index') }}" class="sidebar-link"><i class="mdi mdi-clock-outline"></i><span class="hide-menu">Shifts</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('attendances.index') }}" class="sidebar-link"><i class="mdi mdi-calendar-clock"></i><span class="hide-menu">Attendances</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('overtimes.index') }}" class="sidebar-link"><i class="mdi mdi-timer-sand"></i><span class="hide-menu">Overtime</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('salary-structures.index') }}" class="sidebar-link"><i class="mdi mdi-cash"></i><span class="hide-menu">Salary Structures</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'salary-components') }}" class="sidebar-link"><i class="mdi mdi-cash-plus"></i><span class="hide-menu">Salary Components</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('payroll-periods.index') }}" class="sidebar-link"><i class="mdi mdi-calendar-range"></i><span class="hide-menu">Payroll Periods</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('payrolls.index') }}" class="sidebar-link"><i class="mdi mdi-cash-usd"></i><span class="hide-menu">Payrolls</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('payslips.index') }}" class="sidebar-link"><i class="mdi mdi-file-document-box"></i><span class="hide-menu">Payslips</span></a></li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="mdi mdi-finance"></i><span class="hide-menu">Finance</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item"><a href="{{ route('finance.accounts') }}" class="sidebar-link"><i class="mdi mdi-wallet-membership"></i><span class="hide-menu">Cash &amp; Bank</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('bank-transactions.index') }}" class="sidebar-link"><i class="mdi mdi-bank-transfer"></i><span class="hide-menu">Bank Mutations</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('bank-reconciliations.index') }}" class="sidebar-link"><i class="mdi mdi-checkbox-multiple-marked-outline"></i><span class="hide-menu">Reconciliations</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('bills.index') }}" class="sidebar-link"><i class="mdi mdi-file-document"></i><span class="hide-menu">Bills</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('bill-templates.index') }}" class="sidebar-link"><i class="mdi mdi-file-outline"></i><span class="hide-menu">Bill Templates</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'expense-categories') }}" class="sidebar-link"><i class="mdi mdi-tag-text-outline"></i><span class="hide-menu">Expense Categories</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('expenses.index') }}" class="sidebar-link"><i class="mdi mdi-cash-minus"></i><span class="hide-menu">Expenses</span></a></li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                            <i class="mdi mdi-folder-multiple"></i><span class="hide-menu">Asset &amp; Document</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item"><a href="{{ route('assets.index') }}" class="sidebar-link"><i class="mdi mdi-desktop-classic"></i><span class="hide-menu">Assets</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('media.index') }}" class="sidebar-link"><i class="mdi mdi-image-multiple"></i><span class="hide-menu">Media Files</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('digital-accounts.index') }}" class="sidebar-link"><i class="mdi mdi-key-variant"></i><span class="hide-menu">Digital Accounts</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'asset-categories') }}" class="sidebar-link"><i class="mdi mdi-shape-outline"></i><span class="hide-menu">Asset Categories</span></a></li>
                            <li class="sidebar-item"><a href="{{ route('masterdata.index', 'digital-account-categories') }}" class="sidebar-link"><i class="mdi mdi-cellphone-link"></i><span class="hide-menu">Digital Account Categories</span></a></li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link waves-effect waves-dark {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}" aria-expanded="false">
                            <i class="mdi mdi-chart-bar"></i><span class="hide-menu">Reports</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link waves-effect waves-dark {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" aria-expanded="false">
                            <i class="mdi mdi-account-key"></i><span class="hide-menu">Users</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link waves-effect waves-dark {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}" aria-expanded="false">
                            <i class="mdi mdi-shield-account"></i><span class="hide-menu">Roles</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    @endauth

    <div class="page-wrapper">
        <div class="page-breadcrumb">
            <div class="row">
                <div class="col-12 d-flex no-block align-items-center">
                    <h4 class="page-title">@yield('title', 'Dashboard')</h4>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @yield('content')
            <footer class="footer text-center">ERP Dian Mustika</footer>
        </div>
    </div>

    <script src="{{ asset('theme/matrix-admin/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('theme/matrix-admin/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('theme/matrix-admin/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('theme/matrix-admin/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('theme/matrix-admin/dist/js/waves.js') }}"></script>
    <script src="{{ asset('theme/matrix-admin/dist/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('theme/matrix-admin/dist/js/custom.min.js') }}"></script>
    <script>
        $(function () { $('.preloader').fadeOut(); });
    </script>
    @stack('scripts')
</div>
</body>
</html>
