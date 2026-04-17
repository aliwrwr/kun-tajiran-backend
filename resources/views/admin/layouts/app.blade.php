<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - كن تاجرا</title>

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Google Fonts Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1d4ed8;
            --secondary: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --sidebar-width: 260px;
        }

        * { font-family: 'Cairo', sans-serif !important; }
        body { background: #F1F5F9; min-height: 100vh; }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            position: fixed;
            right: 0;
            top: 0;
            z-index: 1000;
            transition: transform .3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            flex-shrink: 0;
        }

        .sidebar-brand h5 {
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            margin: 0;
        }

        .sidebar-brand span {
            color: #10B981;
            font-size: .75rem;
        }

        .sidebar-nav {
            padding: .5rem 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Scrollbar styling for sidebar */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.3); }

        .nav-section-title {
            font-size: .65rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 1rem 1.25rem .25rem;
            font-weight: 700;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 1.25rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: .5rem;
            margin: .1rem .5rem;
            font-size: .88rem;
            font-weight: 500;
            transition: all .2s;
        }

        .sidebar-item:hover, .sidebar-item.active {
            background: rgba(37, 99, 235, .15);
            color: #fff;
        }

        .sidebar-item.active { color: #60a5fa; }
        .sidebar-item i { font-size: 1.1rem; width: 20px; text-align: center; }

        .sidebar-badge {
            margin-right: auto;
            background: var(--danger);
            color: #fff;
            font-size: .65rem;
            padding: .1rem .45rem;
            border-radius: 99px;
            font-weight: 700;
        }

        /* ── Main content ── */
        #main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar h4 {
            margin: 0;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 700;
        }

        .content-area { padding: 1.5rem; flex: 1; }

        /* ── Cards ── */
        .stat-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
        }

        .stat-card .icon-box {
            width: 52px;
            height: 52px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ── Tables ── */
        .table-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            overflow: hidden;
        }

        .table th { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; background: #f8fafc; }
        .table td { font-size: .88rem; vertical-align: middle; }

        /* ── Badge overrides ── */
        .badge.bg-new          { background: #dbeafe !important; color: #1d4ed8; }
        .badge.bg-confirmed    { background: #d1fae5 !important; color: #065f46; }
        .badge.bg-preparing    { background: #fef3c7 !important; color: #92400e; }
        .badge.bg-out          { background: #ede9fe !important; color: #5b21b6; }
        .badge.bg-delivered    { background: #d1fae5 !important; color: #065f46; }
        .badge.bg-rejected     { background: #fee2e2 !important; color: #991b1b; }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-right: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <h5>🏪 كن تاجرا</h5>
        <span>لوحة الإدارة</span>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-title">الرئيسية</div>
        <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> لوحة التحكم
        </a>

        <div class="nav-section-title">المنتجات</div>
        <a href="{{ route('admin.products.index') }}" class="sidebar-item {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i> إدارة المنتجات
        </a>
        <a href="{{ route('admin.products.create') }}" class="sidebar-item">
            <i class="bi bi-plus-circle-fill"></i> إضافة منتج
        </a>
        <a href="{{ route('admin.categories.index') }}" class="sidebar-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> الأقسام
        </a>

        <div class="nav-section-title">الطلبات</div>
        <a href="{{ route('admin.orders.index') }}" class="sidebar-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
            <i class="bi bi-bag-fill"></i> إدارة الطلبات
            @php $pendingOrders = \App\Models\Order::where('status','new')->count(); @endphp
            @if($pendingOrders > 0)
                <span class="sidebar-badge">{{ $pendingOrders }}</span>
            @endif
        </a>

        <div class="nav-section-title">الأشخاص</div>
        <a href="{{ route('admin.users.index') }}" class="sidebar-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> البائعون
        </a>
        <a href="{{ route('admin.delivery.index') }}" class="sidebar-item {{ request()->routeIs('admin.delivery*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> مناديب التوصيل
        </a>

        <a href="{{ route('admin.delivery-zones.index') }}" class="sidebar-item {{ request()->routeIs('admin.delivery-zones*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i> أجور التوصيل
        </a>

        <div class="nav-section-title">المالية</div>
        <a href="{{ route('admin.profits.index') }}" class="sidebar-item {{ request()->routeIs('admin.profits*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i> تقرير الأرباح
        </a>
        <a href="{{ route('admin.withdrawals.index') }}" class="sidebar-item {{ request()->routeIs('admin.withdrawals*') ? 'active' : '' }}">
            <i class="bi bi-cash-coin"></i> طلبات السحب
            @php $pendingW = \App\Models\WithdrawalRequest::where('status','pending')->count(); @endphp
            @if($pendingW > 0)
                <span class="sidebar-badge">{{ $pendingW }}</span>
            @endif
        </a>
        <a href="{{ route('admin.notifications.index') }}" class="sidebar-item {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
            <i class="bi bi-bell-fill"></i> الإشعارات
        </a>

        <div class="nav-section-title">الشاشة الرئيسية</div>
        <a href="{{ route('admin.banners.index') }}" class="sidebar-item {{ request()->routeIs('admin.banners*') ? 'active' : '' }}">
            <i class="bi bi-images"></i> البانرات الإعلانية
        </a>
        <a href="{{ route('admin.home-settings.index') }}" class="sidebar-item {{ request()->routeIs('admin.home-settings*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> إعدادات الشاشة الرئيسية
        </a>

        <div class="nav-section-title">الحساب</div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="sidebar-item border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
            </button>
        </form>
    </div>
</nav>

<!-- Main Content -->
<div id="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <h4>@yield('page-title', 'لوحة التحكم')</h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">{{ auth()->user()->name }}</span>
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width:34px;height:34px;font-size:.85rem;">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
        </div>
    </div>

    <!-- Flash messages -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <div class="content-area">
        @yield('content')
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
