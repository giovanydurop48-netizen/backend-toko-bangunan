@extends('layouts.admin')
@section('title', 'Manajemen Pengguna & Hak Akses')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Tambah Akun Baru</div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email Login</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role / Jabatan</label>
                        <select name="role" class="form-select" required>
                            <option value="kasir">Kasir (Akses POS Mobile/Web)</option>
                            <option value="admin">Admin (Kelola Gudang & Toko)</option>
                            <option value="owner">Owner (Laporan Omset & Laba)</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Buat Akun</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td class="fw-bold">{{ $u->nama }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge bg-{{ $u->role == 'owner' ? 'dark' : ($u->role == 'admin' ? 'primary' : 'info') }}">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUser{{ $u->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if(auth()->id() != $u->id)
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Edit User -->
                        <div class="modal fade" id="editUser{{ $u->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.users.update', $u->id) }}" method="POST" class="modal-content">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title">Edit Pengguna</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="nama" class="form-control" value="{{ $u->nama }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Role</label>
                                            <select name="role" class="form-select">
                                                <option value="kasir" {{ $u->role == 'kasir' ? 'selected' : '' }}>Kasir</option>
                                                <option value="admin" {{ $u->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="owner" {{ $u->role == 'owner' ? 'selected' : '' }}>Owner</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                            <input type="password" name="password" class="form-control">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('admin.partials.alur-nav', ['step' => 1])
@endsection