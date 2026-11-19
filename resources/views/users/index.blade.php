@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Kelola Pengguna Sistem</h3>
        <p class="text-muted small">Tambah akun kasir baru, admin, atau owner toko.</p>
    </div>
    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
        <i class="bi bi-person-plus me-1"></i> Tambah User Baru
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Role / Hak Akses</th>
                    <th>Tanggal Dibuat</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="fw-bold">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->role === 'owner')
                            <span class="badge bg-purple bg-dark">OWNER</span>
                        @elseif($user->role === 'admin')
                            <span class="badge bg-primary">ADMIN</span>
                        @else
                            <span class="badge bg-info text-dark">KASIR</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        @if(Auth::id() !== $user->id)
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun pengguna ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                        </form>
                        @else
                        <span class="badge bg-light text-muted">Akun Anda</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Akun Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="kasir">Kasir (Khusus Aplikasi POS)</option>
                            <option value="admin">Admin (Web Dashboard)</option>
                            <option value="owner">Owner (Web Dashboard & Laporan)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection