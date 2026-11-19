@php
    $steps = [
        1 => ['title' => 'User', 'route' => 'admin.users.index'],
        2 => ['title' => 'Produk', 'route' => 'admin.produk.index'],
        3 => ['title' => 'Stok', 'route' => 'admin.stok.index'],
        4 => ['title' => 'Transaksi', 'route' => 'admin.transaksi.index'],
        5 => ['title' => 'Keuangan', 'route' => 'admin.keuangan.index'],
    ];

    $currentStep =$step ?? 1;
    $prevStep    = $steps[$currentStep - 1] ?? null;
    $nextStep    = $steps[$currentStep + 1] ?? null;
@endphp

<div class="card border-0 shadow-sm mt-3 bg-white">
    <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <!-- Tombol Kiri -->
        <div>
            @if($prevStep)
                <a href="{{ route($prevStep['route']) }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-arrow-left me-1"></i> {{ $prevStep['title'] }}
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            @endif
        </div>

        <!-- Indikator Langkah -->
        <div class="d-flex flex-wrap align-items-center gap-1">
            @foreach($steps as $num =>$item)
                <a href="{{ route($item['route']) }}"
                   class="badge text-decoration-none py-1 px-2 {{ $num ==$currentStep ? 'bg-primary text-white' : ($num <$currentStep ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border') }}">
                    {{ $num }}. {{$item['title'] }}
                </a>
            @endforeach
        </div>

        <!-- Tombol Kanan -->
        <div>
            @if($nextStep)
                <a href="{{ route($nextStep['route']) }}" class="btn btn-primary btn-sm px-3 fw-semibold">
                    Lanjut: {{ $nextStep['title'] }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="btn btn-success btn-sm px-3 fw-semibold">
                    <i class="bi bi-check-lg me-1"></i> Selesai
                </a>
            @endif
        </div>
    </div>
</div>