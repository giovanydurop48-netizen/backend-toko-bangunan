<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Owner') - TB. Maju Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <script>
        const savedTheme = localStorage.getItem('tb_theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>

    <style>
        :root {
            --bs-primary: #78350F;
            --bs-primary-rgb: 120, 53, 15;
            --brown-main: #78350F;
            --brown-hover: #5C280B;
            --brown-accent: #B45309;
            --brown-tan: #D6A874;
            --brown-soft: #F3E8D8;
            --brown-text: #451A03;
        }

        /* Sidebar Owner: Abu-abu Charcoal + Aksen Cokelat */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #292524 0%, #1C1917 100%);
            color: #F5F5F4;
            border-right: 3px solid #92400E;
        }

        .sidebar a {
            color: #D6D3D1;
            text-decoration: none;
            padding: 10px 14px;
            display: block;
            border-radius: 8px;
            margin-bottom: 4px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .sidebar a:hover {
            background: rgba(180, 83, 9, 0.22);
            color: #FDE68A;
        }

        .sidebar a.active {
            background: linear-gradient(90deg, #78350F 0%, #B45309 100%);
            color: #FFFFFF;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(120, 53, 15, 0.45);
        }

        .btn-primary {
            background-color: var(--brown-main) !important;
            border-color: var(--brown-main) !important;
            color: #FFFFFF !important;
        }

        .btn-primary:hover {
            background-color: var(--brown-hover) !important;
            border-color: var(--brown-accent) !important;
        }

        .btn-outline-primary {
            color: var(--brown-main) !important;
            border-color: var(--brown-main) !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--brown-main) !important;
            color: #FFFFFF !important;
        }

        .text-primary {
            color: var(--brown-main) !important;
        }

        .btn-brown {
            background: linear-gradient(90deg, #78350F 0%, #92400E 100%) !important;
            border: 1px solid #B45309 !important;
            color: #FFFFFF !important;
            font-weight: 600 !important;
        }

        /* Mode Terang */
        [data-bs-theme="light"] body {
            background-color: #F4F3F1;
            color: #292524;
        }

        /* Mode Gelap */
        [data-bs-theme="dark"] body {
            background-color: #141210;
            color: #E7E5E4;
        }

        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .list-group-item {
            background-color: #1F1C19 !important;
            color: #E7E5E4 !important;
            border-color: #36302A !important;
        }

        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .bg-white {
            background-color: #1F1C19 !important;
            color: #E7E5E4 !important;
            border-color: #36302A !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: #292521 !important;
            color: #D6D3D1 !important;
        }

        [data-bs-theme="dark"] .table {
            --bs-table-bg: #1F1C19;
            --bs-table-color: #E7E5E4;
            --bs-table-hover-bg: #2B2622;
            --bs-table-border-color: #36302A;
        }

        [data-bs-theme="dark"] .table-light {
            --bs-table-bg: #292521;
            --bs-table-color: #D6A874;
            --bs-table-border-color: #443C35;
        }

        [data-bs-theme="dark"] .text-dark {
            color: #F5F5F4 !important;
        }

        [data-bs-theme="dark"] .text-muted {
            color: #A8A29E !important;
        }

        [data-bs-theme="dark"] .text-primary {
            color: #D6A874 !important;
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #171412 !important;
            border-color: #443C35 !important;
            color: #F5F5F4 !important;
        }

        /* Pengaturan Khusus Saat Dicetak (Print Laporan) */
        @media print {
            .no-print, .sidebar {
                display: none !important;
            }
            main {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body {
                background: #FFFFFF !important;
                color: #000000 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #CCCCCC !important;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Owner -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3 d-flex flex-column justify-content-between no-print">
            <div>
                <h5 class="fw-bold text-white mb-1">
                    <i class="bi bi-building me-2" style="color: #D6A874;"></i>TB. Maju Jaya
                </h5>
                <small class="d-block mb-2" style="color: #D6A874;">Portal Pemilik (Owner)</small>

                <!-- Tombol Mode Terang / Gelap -->
                <button type="button" id="btnThemeToggle" class="btn btn-sm w-100 mb-3 d-flex align-items-center justify-content-center gap-2"
                        style="background: rgba(180, 83, 9, 0.2); border: 1px solid #B45309; color: #F3E8D8;">
                    <i id="themeIcon" class="bi bi-moon-stars-fill" style="color: #D6A874;"></i>
                    <span id="themeText" class="small fw-semibold">Mode Gelap</span>
                </button>

                <!-- Menu Owner -->
                <a href="{{ route('owner.dashboard') }}" class="{{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow me-2"></i> Ringkasan Bisnis
                </a>
                <a href="{{ route('owner.laporan') }}" class="{{ request()->routeIs('owner.laporan') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan Keuangan
                </a>
                <a href="{{ route('owner.transaksi') }}" class="{{ request()->routeIs('owner.transaksi') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff me-2"></i> Riwayat Penjualan
                </a>
                <a href="{{ route('owner.stok') }}" class="{{ request()->routeIs('owner.stok') ? 'active' : '' }}">
                    <i class="bi bi-boxes me-2"></i> Pantau Aset &amp; Stok
                </a>
            </div>

            <!-- Tombol Keluar -->
            <div class="pt-3 mt-3 border-top border-secondary">
                <form action="{{ Route::has('logout') ? route('logout') : url('/logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-left me-1"></i> Keluar
                    </button>
                </form>
            </div>
        </nav>

        <!-- Konten Utama -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
            <!-- Header Atas -->
            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom no-print">
                <h5 class="fw-bold mb-0">@yield('title', 'Ringkasan Bisnis Owner')</h5>
                <span class="badge btn-brown py-2 px-3">
                    <i class="bi bi-award-fill me-1"></i> {{ auth()->user()->name ?? 'Owner Toko' }}
                </span>
            </div>

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnToggle = document.getElementById('btnThemeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');

        function applyThemeUI(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun-fill';
                themeText.textContent = 'Mode Terang';
            } else {
                themeIcon.className = 'bi bi-moon-stars-fill';
                themeText.textContent = 'Mode Gelap';
            }
        }

        const currentTheme = localStorage.getItem('tb_theme') || 'light';
        applyThemeUI(currentTheme);

        if (btnToggle) {
            btnToggle.addEventListener('click', function () {
                const activeTheme = document.documentElement.getAttribute('data-bs-theme');
                const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('tb_theme', nextTheme);
                applyThemeUI(nextTheme);
            });
        }
    });
</script>
</body>
</html>