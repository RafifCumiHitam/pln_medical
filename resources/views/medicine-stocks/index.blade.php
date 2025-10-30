@extends('layouts.admin')

@section('title', 'Stok Obat Terakhir')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 text-gray-800 mb-0">Stok Obat Terakhir</h1>
    <div>
        <a href="{{ route('medicine-stocks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-2"></i>Input Stok Obat
        </a>
        <a href="{{ route('medicine-stocks.logs') }}" class="btn btn-info">
            <i class="fas fa-clipboard-list mr-2"></i>Lihat Log Obat
        </a>
        <a href="{{ route('medicines.create') }}" class="btn btn-success">
            <i class="fas fa-pills mr-2"></i>Input Obat
        </a>
    </div>
</div>

{{-- 🔔 Notifikasi stok menipis / habis / kadaluarsa --}}
@php
    use Carbon\Carbon;
    $lowStock = $medicines->filter(fn($m) => $m->status_stok === 'menipis');
    $outStock = $medicines->filter(fn($m) => $m->status_stok === 'habis');
    $almostExpired = $medicines->filter(fn($m) => $m->expired_date && Carbon::parse($m->expired_date)->diffInDays(now()) <= 30 && Carbon::parse($m->expired_date)->isFuture());
    $expired = $medicines->filter(fn($m) => $m->expired_date && Carbon::parse($m->expired_date)->isPast());
@endphp

@if($lowStock->count())
<div class="alert alert-warning shadow-sm">
    <strong><i class="fas fa-exclamation-triangle mr-1"></i> Peringatan!</strong> Beberapa obat mulai menipis:
    <ul class="mb-0 mt-1">
        @foreach($lowStock as $med)
        <li>{{ $med->nama_obat }} — Stok: <strong>{{ $med->stok_akhir }}</strong>, Minimum: <strong>{{ $med->stok_minim }}</strong></li>
        @endforeach
    </ul>
</div>
@endif

@if($outStock->count())
<div class="alert alert-danger shadow-sm">
    <strong><i class="fas fa-times-circle mr-1"></i> Peringatan!</strong> Beberapa obat sudah habis:
    <ul class="mb-0 mt-1">
        @foreach($outStock as $med)
        <li>{{ $med->nama_obat }} — Stok: <strong>{{ $med->stok_akhir }}</strong></li>
        @endforeach
    </ul>
</div>
@endif

@if($almostExpired->count())
<div class="alert alert-warning shadow-sm">
    <strong><i class="fas fa-hourglass-half mr-1"></i> Peringatan!</strong> Beberapa obat hampir kadaluarsa (≤30 hari):
    <ul class="mb-0 mt-1">
        @foreach($almostExpired as $med)
        <li>{{ $med->nama_obat }} — Kadaluarsa: <strong>{{ Carbon::parse($med->expired_date)->format('d-m-Y') }}</strong></li>
        @endforeach
    </ul>
</div>
@endif

@if($expired->count())
<div class="alert alert-danger shadow-sm">
    <strong><i class="fas fa-skull-crossbones mr-1"></i> Bahaya!</strong> Beberapa obat sudah <u>kadaluarsa</u>:
    <ul class="mb-0 mt-1">
        @foreach($expired as $med)
        <li>{{ $med->nama_obat }} — Kadaluarsa: <strong>{{ Carbon::parse($med->expired_date)->format('d-m-Y') }}</strong></li>
        @endforeach
    </ul>
</div>
@endif

{{-- Table tanpa scroll --}}
<div class="card shadow mb-4 border-left-primary">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Obat</h6>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
            <thead class="thead-light">
                <tr>
                    <th>ID<i class="fas fa-sort ml-1 text-gray-400"></th>
                    <th>Nama Obat<i class="fas fa-sort ml-1 text-gray-400"></th>
                    <th>Kategori<i class="fas fa-sort ml-1 text-gray-400"></th>
                    <th>Satuan<i class="fas fa-sort ml-1 text-gray-400"></th>
                    <th>Stok Terakhir<i class="fas fa-sort ml-1 text-gray-400"></th>
                    <th>Stok Minimum<i class="fas fa-sort ml-1 text-gray-400"></th>
                    <th>Kadaluarsa<i class="fas fa-sort ml-1 text-gray-400"></th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicines as $med)
                @php
                    $expiredDate = $med->expired_date ? Carbon::parse($med->expired_date) : null;
                    $isExpired = $expiredDate && $expiredDate->isPast();
                    $isAlmostExpired = $expiredDate && $expiredDate->diffInDays(now()) <= 30 && !$isExpired;

                    $rowClass = match(true) {
                        $isExpired => 'table-danger',
                        $med->status_stok === 'habis' => 'table-danger',
                        $med->status_stok === 'menipis' => 'table-warning',
                        $isAlmostExpired => 'table-warning-expired',
                        default => ''
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $med->id }}</td>
                    <td>{{ $med->nama_obat }}</td>
                    <td>{{ $med->kategori }}</td>
                    <td>{{ $med->satuan }}</td>
                    <td>{{ $med->stok_akhir }}</td>
                    <td>{{ $med->stok_minim ?? '-' }}</td>
                    <td>{{ $expiredDate ? $expiredDate->format('d-m-Y') : '-' }}</td>
                    <td class="text-center">
                        <a href="{{ route('medicines.edit', $med->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('medicines.destroy', $med->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Apakah yakin ingin menghapus obat ini?')">
                                <i class="fas fa-trash-alt"></i>
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

@push('styles')
<style>
.table-warning { background-color: #fff3cd !important; }
.table-danger { background-color: #f8d7da !important; }
.table-warning-expired { background-color: #ffe5b4 !important; }
.alert-warning { border-left: 6px solid #ffca2c; }
.alert-danger { border-left: 6px solid #dc3545; }

/* Konsistensi DataTable UI dengan visitors page */
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
.dataTables_info {
    color: #858796;
    padding-top: 10px;
}
</style>
@endpush

@push('scripts')
<script>
    $(function() {
        // DataTables setup
        $('#dataTable').DataTable({
            paging: true,
            searching: true,
            responsive: true,
            pageLength: 10,
            order: [[0, 'asc']],
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
                { orderable: false, targets: [4, 7] }
            ]
        });
});
</script>
@endpush