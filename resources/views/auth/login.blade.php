<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Portal — TB. Maju Jaya</title>

    <!-- Bootstrap 5 & Bootstrap Icons (selaras dengan Dashboard Admin & Owner) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --tb-bg: #F4F1EC;
            --tb-card: #FFFFFF;
            --tb-sidebar: #231810;
            --tb-sidebar-accent: #332318;
            --tb-primary: #9A4E16;
            --tb-primary-hover: #7D3E10;
            --tb-gold: #F59E0B;
            --tb-text: #1F1915;
            --tb-muted: #6E655F;
            --tb-border: #E5DFD6;
            --tb-input-bg: #FAF8F5;
        }

        [data-bs-theme="dark"] {
            --tb-bg: #140E0A;
            --tb-card: #1F1610;
            --tb-sidebar: #18100B;
            --tb-sidebar-accent: #2B1D14;
            --tb-primary: #B45D1E;
            --tb-primary-hover: #9A4E16;
            --tb-gold: #FBBF24;
            --tb-text: #F5EFEA;
            --tb-muted: #A89E96;
            --tb-border: #36281E;
            --tb-input-bg: #271C14;
        }

        body {
            background-color: var(--tb-bg);
            color: var(--tb-text);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .login-wrapper {
            width: 100%;
            max-width: 880px;
            background-color: var(--tb-card);
            border: 1px solid var(--tb-border);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(35, 24, 16, 0.10);
        }

        /* Panel Kiri (Identik dengan Sidebar TB. Maju Jaya) */
        .brand-panel {
            background: linear-gradient(165deg, var(--tb-sidebar) 0%, var(--tb-sidebar-accent) 100%);
            color: #FFFFFF;
            padding: 2.75rem 2.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .brand-logo-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background-color: rgba(245, 158, 11, 0.16);
            border: 1px solid rgba(245, 158, 11, 0.35);
            color: var(--tb-gold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .brand-title {
            color: var(--tb-gold);
            font-weight: 700;
            font-size: 1.45rem;
            letter-spacing: -0.3px;
        }

        .feature-pill {
            background-color: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 10px;
            padding: 0.7rem 0.9rem;
            font-size: 0.85rem;
            color: #EAE2DA;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .feature-pill i {
            color: var(--tb-gold);
            font-size: 1rem;
        }

        /* Panel Kanan (Form Login) */
        .form-panel {
            padding: 2.75rem 2.5rem;
            background-color: var(--tb-card);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--tb-text);
            margin-bottom: 0.4rem;
        }

        .form-control {
            background-color: var(--tb-input-bg);
            border: 1.5px solid var(--tb-border);
            color: var(--tb-text);
            border-radius: 10px;
            padding: 0.7rem 0.95rem;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            background-color: var(--tb-card);
            border-color: var(--tb-primary);
            color: var(--tb-text);
            box-shadow: 0 0 0 0.2rem rgba(154, 78, 22, 0.18);
        }

        .input-group .btn-toggle-pass {
            background-color: var(--tb-input-bg);
            border: 1.5px solid var(--tb-border);
            border-left: 0;
            color: var(--tb-muted);
            border-radius: 0 10px 10px 0;
            padding: 0 0.9rem;
        }

        .input-group .form-control {
            border-right: 0;
            border-radius: 10px 0 0 10px;
        }

        .btn-tb-primary {
            background-color: var(--tb-primary);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            letter-spacing: 0.4px;
            padding: 0.78rem 1.25rem;
            border-radius: 10px;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .btn-tb-primary:hover,
        .btn-tb-primary:focus {
            background-color: var(--tb-primary-hover);
            color: #FFFFFF;
        }

        .btn-tb-primary:active {
            transform: scale(0.99);
        }

        .btn-theme-toggle {
            background-color: var(--tb-input-bg);
            border: 1px solid var(--tb-border);
            color: var(--tb-text);
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.35rem 0.75rem;
        }

        .btn-theme-toggle:hover {
            border-color: var(--tb-primary);
            color: var(--tb-primary);
        }
    </style>
</head>
<body>

@php
    $loginAction = url('/login');
    foreach (['login.post', 'login.process', 'auth.login', 'login'] as $routeName) {
        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            $routeObj = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName);
            if ($routeObj && in_array('POST', $routeObj->methods())) {
                $loginAction = route($routeName);
                break;
            }
        }
    }
@endphp

<div class="login-wrapper">
    <div class="row g-0">
        <!-- Kolom Kiri: Branding TB. Maju Jaya -->
        <div class="col-lg-5 brand-panel">
            <div>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-logo-box">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <div class="brand-title">TB. Maju Jaya</div>
                        <small style="color: #C7BBAE;">Portal Manajemen & Kasir</small>
                    </div>
                </div>

                <p class="small mb-4" style="color: #D8CFC6; line-height: 1.6;">
                    Sistem terpadu pengelolaan stok material bangunan, pembelian gudang, transaksi POS kasir, dan laporan keuangan toko.
                </p>

                <div class="d-flex flex-column gap-2">
                    <div class="feature-pill">
                        <i class="bi bi-box-seam"></i>
                        <span>Kontrol Stok & Pembelian Material</span>
                    </div>
                    <div class="feature-pill">
                        <i class="bi bi-receipt"></i>
                        <span>Sinkronisasi Transaksi Kasir POS</span>
                    </div>
                    <div class="feature-pill">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Ringkasan Omzet & Laba Bersih Owner</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-4 border-top" style="border-color: rgba(255,255,255,0.12) !important;">
                <small style="color: #B5A89C;">&copy; {{ date('Y') }} TB. Maju Jaya. Hak Cipta Dilindungi.</small>
            </div>
        </div>

        <!-- Kolom Kanan: Form Login -->
        <div class="col-lg-7 form-panel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--tb-primary);">Dashboard Login</h4>
                    <p class="small mb-0" style="color: var(--tb-muted);">Akses Khusus Admin & Owner Toko</p>
                </div>
                <button type="button" class="btn btn-theme-toggle" id="btnToggleTheme">
                    <i class="bi bi-moon-stars-fill text-warning me-1" id="themeIcon"></i>
                    <span id="themeText">Mode Gelap</span>
                </button>
            </div>

            @if (session('error'))
                <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 small rounded-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $loginAction }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Portal</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="admin@toko.com atau owner@toko.com"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password akun..."
                            required
                        >
                        <button class="btn btn-toggle-pass" type="button" id="btnTogglePass" title="Tampilkan / Sembunyikan Password">
                            <i class="bi bi-eye" id="passIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-tb-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>MASUK DASHBOARD</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Toggle Tampilkan Password
    const btnTogglePass = document.getElementById('btnTogglePass');
    const passInput     = document.getElementById('password');
    const passIcon      = document.getElementById('passIcon');

    btnTogglePass.addEventListener('click', () => {
        const isPassword = passInput.type === 'password';
        passInput.type = isPassword ? 'text' : 'password';
        passIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Toggle Mode Gelap / Terang (Sinkron dengan Tema Dashboard)
    const htmlEl         = document.documentElement;
    const btnToggleTheme = document.getElementById('btnToggleTheme');
    const themeIcon      = document.getElementById('themeIcon');
    const themeText      = document.getElementById('themeText');

    function applyTheme(theme) {
        htmlEl.setAttribute('data-bs-theme', theme);
        localStorage.setItem('tb_theme', theme);
        if (theme === 'dark') {
            themeIcon.className = 'bi bi-sun-fill text-warning me-1';
            themeText.textContent = 'Mode Terang';
        } else {
            themeIcon.className = 'bi bi-moon-stars-fill text-warning me-1';
            themeText.textContent = 'Mode Gelap';
        }
    }

    const savedTheme = localStorage.getItem('tb_theme') || 'light';
    applyTheme(savedTheme);

    btnToggleTheme.addEventListener('click', () => {
        const current = htmlEl.getAttribute('data-bs-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });
</script>
</body>
</html>