@extends('layouts.admin')

@section('title', 'Track Visitor - List')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800 mb-0">Daftar Kunjungan Pasien</h1>
    <a href="{{ route('visitors.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Data Pasien
    </a>
</div>

{{-- Export CSV --}}
<div class="card shadow mb-4 border-left-success">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-success">Export Rekap Kunjungan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('visitors.export') }}" method="GET" id="exportForm" class="form-inline">
            <div class="form-group mb-2 mr-3">
                <label for="start_date" class="mr-2 font-weight-bold text-gray-700">Dari</label>
                <input type="date" name="start_date" id="start_date" class="form-control" required>
            </div>
            <div class="form-group mb-2 mr-3">
                <label for="end_date" class="mr-2 font-weight-bold text-gray-700">Sampai</label>
                <input type="date" name="end_date" id="end_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success mb-2">
                <i class="fas fa-file-excel mr-1"></i> Export XLSX
            </button>
        </form>
    </div>
</div>

{{-- Visitor Table --}}
<div class="card shadow mb-4 border-left-primary">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Pengunjung</h6>
    </div>
    <div class="card-body">
        <!-- Hapus class table-responsive untuk scroll horizontal -->
        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>ID <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Nama <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Tanggal Kunjungan <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Keluhan <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
            <tbody>
                @foreach ($visitors as $visitor)
                <tr>
                    <td>{{ $visitor->id }}</td>
                    <td>
                        @if ($visitor->kategori === 'karyawan')
                            {{ $visitor->detail['nid'] ?? 'N/A' }} - {{ $visitor->detail['nama'] ?? 'N/A' }}
                        @else
                            {{ $visitor->detail['nama'] ?? 'N/A' }}
                        @endif
                    </td>
                    <td>{{ $visitor->tanggal_kunjungan }}</td>
                    <td>{{ $visitor->keluhan }}</td>
                    <td class="text-center">
                        <a href="{{ route('visitors.show', $visitor->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('visitors.edit', $visitor->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('visitors.destroy', $visitor->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // DataTables setup
        $('#dataTable').DataTable({
            paging: true,
            searching: true,
            responsive: true,
            pageLength: 10,
            order: [[2, 'desc']], // Kolom Tanggal Kunjungan paling baru muncul di atas
            language: {
                search: "Cari:",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                },
                lengthMenu: "Tampilkan data per halaman _MENU_",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data"
            },
            columnDefs: [
                { orderable: false, targets: 4 }
            ]
        });

        // Validasi sebelum export
        $('#exportForm').on('submit', function(e) {
            if (!$('#start_date').val() || !$('#end_date').val()) {
                e.preventDefault();
                alert('Silakan pilih tanggal awal dan akhir terlebih dahulu.');
            }
        });
    });
</script>

<style>
    /* Penyesuaian DataTables agar sesuai dengan SB Admin 2 */
    #dataTable_wrapper .dataTables_filter {
        float: right;
        text-align: left;
    }
    #dataTable_wrapper .dataTables_filter input {
        border-radius: 10px;
        border: 1px solid #d1d3e2;
        padding: 4px 10px;
        margin-right: 20px;
    }
    #dataTable_wrapper .dataTables_paginate {
        float: right;
        margin-top: 10px;
    }

</style>
@endpush
