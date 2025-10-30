@extends('layouts.admin')

@section('title', 'Log Transaksi Obat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 text-gray-800 mb-0">Log Obat</h1>
    <div>
        <a href="{{ route('medicine-stocks.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Stok Obat
        </a>
        <a href="{{ route('medicine-stocks.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus mr-1"></i> Input Stok Obat
        </a>
    </div>
</div>

{{-- Export CSV --}}
<div class="card shadow mb-4 border-left-success">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-success">Export Rekap Rata-rata Pengeluaran Obat</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('medicine-stocks.export') }}" method="GET" id="exportForm" class="form-inline">
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

{{-- Tabel Log Obat --}}
<div class="card shadow mb-4 border-left-primary">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Log Transaksi Obat</h6>
    </div>
    <div class="card-body">
        <div class="table">
            <table class="table table-bordered table-hover align-middle" id="logTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>ID <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Nama Obat <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Jumlah Masuk <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Jumlah Keluar <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Stok Akhir <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Keterangan <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>Tanggal Transaksi <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th>User <i class="fas fa-sort ml-1 text-gray-400"></i></th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocks as $stock)
                    <tr>
                        <td>{{ $stock->id }}</td>
                        <td>{{ $stock->medicine->nama_obat ?? 'N/A' }}</td>
                        <td>{{ $stock->jumlah_masuk ?? 0 }}</td>
                        <td>{{ $stock->jumlah_keluar ?? 0 }}</td>
                        <td>{{ $stock->stok_akhir }}</td>
                        <td>{{ $stock->keterangan ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($stock->tanggal_transaksi)->format('d-m-Y') }}</td>
                        <td>{{ $stock->user->name ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('medicine-stocks.edit', $stock->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('medicine-stocks.destroy', $stock->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Apakah yakin ingin menghapus log ini?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <!-- <tfoot class="font-weight-bold bg-light">
                    <tr>
                        <td colspan="3" class="text-right">Rata-rata Pengeluaran</td>
                        <td>
                            {{ number_format($stocks->avg('jumlah_keluar'), 2) }}
                        </td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot> -->
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
#logTable_wrapper .dataTables_filter {
    float: right;
    text-align: left;
}
#logTable_wrapper .dataTables_filter input {
    border-radius: 10px;
    border: 1px solid #d1d3e2;
    padding: 4px 10px;
    margin-right: 20px;
}
#logTable_wrapper .dataTables_paginate {
    float: right;
    margin-top: 10px;
}
.DataTables_info {
    color: #858796;
    padding-top: 10px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#logTable').DataTable({
        paging: true,
        searching: true,
        pageLength: 10,
        order: [[6, 'desc']], // Sorting berdasarkan tanggal transaksi terbaru
        language: {
            search: "Cari:",
            paginate: { previous: "Sebelumnya", next: "Berikutnya" },
            lengthMenu: "Tampilkan data per halaman _MENU_",
            zeroRecords: "Tidak ada data yang cocok",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data"
        },
        columnDefs: [
            { orderable: false, targets: [8] } // Kolom aksi tidak bisa di-sort
        ]
    });

    // Validasi export form
    $('#exportForm').on('submit', function(e) {
        if (!$('#start_date').val() || !$('#end_date').val()) {
            e.preventDefault();
            alert('Silakan pilih tanggal awal dan akhir terlebih dahulu.');
        }
    });
});
</script>
@endpush
