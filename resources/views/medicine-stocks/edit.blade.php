@extends('layouts.admin')

@section('title', 'Edit Medicine Stock')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Edit Medicine Stock</h1>
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Stock Record</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicine-stocks.update', $stock->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Medicine</label>
                    <select name="medicine_id" class="form-control" required>
                        @foreach ($medicines as $medicine)
                            <option value="{{ $medicine->id }}" {{ $stock->medicine_id == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->nama_obat }} ({{ $medicine->kode_obat }}) - {{ $medicine->kategori }} ({{ $medicine->satuan }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Masuk</label>
                    <input type="number" name="jumlah_masuk" class="form-control" value="{{ $stock->jumlah_masuk ?? 0 }}" min="0">
                </div>
                <div class="form-group">
                    <label>Jumlah Keluar</label>
                    <input type="number" name="jumlah_keluar" class="form-control" value="{{ $stock->jumlah_keluar ?? 0 }}" min="0">
                </div>
                <div class="form-group">
                    <label>Tanggal Transaksi</label>
                    <input type="date" name="tanggal_transaksi" class="form-control" value="{{ $stock->tanggal_transaksi }}" required>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control">{{ $stock->keterangan ?? '' }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('medicine-stocks.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection