@extends('layouts.admin')

@section('title', 'Edit Obat')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Edit Data Obat</h1>
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Obat</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicines.update', $medicine->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nama Obat</label>
                    <input type="text" name="nama_obat" class="form-control" value="{{ $medicine->nama_obat }}" required>
                </div>
                <div class="form-group">
                    <label>Kode Obat</label>
                    <input type="text" name="kode_obat" class="form-control" value="{{ $medicine->kode_obat }}" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="Pil" {{ $medicine->kategori == 'Pil' ? 'selected' : '' }}>Pil</option>
                        <option value="Salep" {{ $medicine->kategori == 'Salep' ? 'selected' : '' }}>Salep</option>
                        <option value="Sirup" {{ $medicine->kategori == 'Sirup' ? 'selected' : '' }}>Sirup</option>
                        <option value="Injeksi" {{ $medicine->kategori == 'Injeksi' ? 'selected' : '' }}>Injeksi</option>
                        <option value="Lainnya" {{ $medicine->kategori == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <select name="satuan" class="form-control" required>
                        <option value="tablet" {{ $medicine->satuan == 'tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="salep" {{ $medicine->satuan == 'salep' ? 'selected' : '' }}>Salep</option>
                        <option value="ml" {{ $medicine->satuan == 'ml' ? 'selected' : '' }}>ml</option>
                        <option value="tube" {{ $medicine->satuan == 'tube' ? 'selected' : '' }}>Tube</option>
                        <option value="vial" {{ $medicine->satuan == 'vial' ? 'selected' : '' }}>Vial</option>
                        <option value="lainnya" {{ $medicine->satuan == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stok Minim</label>
                    <input type="number" name="stok_minim" class="form-control" value="{{ $medicine->stok_minim }}" min="0" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Kadaluarsa</label>
                    <input type="date" name="expired_date" class="form-control" value="{{ $medicine->expired_date }}" required>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('medicine-stocks.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection