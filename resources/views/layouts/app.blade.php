<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Toko Bangunan')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #1e293b; color: #fff; }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 20px; font-weight: 500; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: #334155; border-radius: 6px; }
        .stat-card { border: none; border-radius: 12px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 sidebar p-3 d-flex flex-column">
                <h4 class="text-white fw-bold px-2 mb-4"><i class="bi bi-building"></i> TB Bangunan</h4>
                <div class="small text-uppercase px-2 text-muted mb-2">Menu Utaman</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item mb-1">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    @if(Auth::user()->role === 'admin')
                    <li class="nav-item mb-1">
                        <a href="{{ route('produk.index') }}" class="nav-link {{ request()->is('produk*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam me-2"></i> Kelola Produk
                        </a>
                    </li>
                    @endif
                    <li class="nav-item mb-1">
                        <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->is('laporan*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan Keuangan
                        </a>
                    </li>
                </ul>
                <hr class="text-secondary">
                <div class="px-2">
                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                    <span class="badge bg-primary text-uppercase">{{ Auth::user()->role }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-box-arrow-right"></i> Keluar</button>
                    </form>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9 col-lg-10 p-4">
                @yield('content')
            </div>
        </div>
    </div>
    <script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>