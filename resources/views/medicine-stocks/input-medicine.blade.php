@extends('layouts.admin')

@section('title', 'Input Obat - Create')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Input Data Obat</h1>
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Input Obat</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicines.store') }}">
                @csrf
                <div class="form-group">
                    <label>Nama Obat</label>
                    <input type="text" name="nama_obat" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kode Obat</label>
                    <input type="text" name="kode_obat" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="Pil">Pil</option>
                        <option value="Salep">Salep</option>
                        <option value="Sirup">Sirup</option>
                        <option value="Injeksi">Injeksi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <select name="satuan" class="form-control" required>
                        <option value="tablet">Tablet</option>
                        <option value="salep">Salep</option>
                        <option value="ml">ml</option>
                        <option value="tube">Tube</option>
                        <option value="vial">Vial</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stok Minim</label>
                    <input type="number" name="stok_minim" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Kadaluarsa</label>
                    <input type="date" name="expired_date" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('medicine-stocks.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection