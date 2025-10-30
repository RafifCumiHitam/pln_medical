@extends('layouts.admin')

@section('title', 'Detail Visitor')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Detail Visitor</h1>

<div class="card shadow mb-4 border-left-primary">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informasi Visitor</h6>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <td>{{ $visitor->id }}</td>
            </tr>
            <tr>
                <th>Kategori</th>
                <td>{{ ucfirst($visitor->kategori) }}</td>
            </tr>
            <tr>
                <th>Detail</th>
                <td>
                    @if ($visitor->kategori === 'karyawan')
                        <strong>NID:</strong> {{ $visitor->detail['nid'] ?? 'N/A' }} <br>
                        <strong>Nama:</strong> {{ $visitor->detail['nama'] ?? 'N/A' }}
                    @else
                        <strong>Nama:</strong> {{ $visitor->detail['nama'] ?? 'N/A' }} <br>
                        <strong>Asal Perusahaan:</strong> {{ $visitor->detail['asal'] ?? 'N/A' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Tanggal Kunjungan</th>
                <td>{{ \Carbon\Carbon::parse($visitor->tanggal_kunjungan)->format('d M Y') }}</td>
            </tr>
            <tr>
                <th>Keluhan</th>
                <td>{{ $visitor->keluhan }}</td>
            </tr>
            <tr>
                <th>Diagnosis</th>
                <td>{{ $visitor->diagnosis ?? '-' }}</td>
            </tr>
            <tr>
                <th>Tindakan</th>
                <td>{{ $visitor->tindakan ?? '-' }}</td>
            </tr>
            <tr>
                <th>Cek Tensi</th>
                <td>{{ $visitor->cek_tensi ?? '-' }}</td>
            </tr>
            <tr>
                <th>Cek Suhu Badan</th>
                <td>{{ $visitor->cek_suhu ? $visitor->cek_suhu . ' °C' : '-' }}</td>
            </tr>

            {{-- 🔁 Revisi: Ganti Cek EKG menjadi Heart Rate dan Respiratory Rate --}}
            <tr>
                <th>Heart Rate (BPM)</th>
                <td>
                    {{ $visitor->heart_rate ? $visitor->heart_rate . ' BPM' : '-' }}
                </td>
            </tr>
            <tr>
                <th>Respiratory Rate (x/menit)</th>
                <td>
                    {{ $visitor->respiratory_rate ? $visitor->respiratory_rate . ' x/menit' : '-' }}
                </td>
            </tr>
            {{-- 🔁 End Revisi --}}

            <tr>
                <th>Ditangani Oleh</th>
                <td>{{ $visitor->user->nama_lengkap ?? '-' }}</td>
            </tr>

            {{-- Resep Obat --}}
            <tr>
                <th>Resep Obat</th>
                <td>
                    @if ($visitor->prescriptions->isEmpty())
                        <em class="text-muted">Tidak ada resep obat.</em>
                    @else
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Obat</th>
                                    <th>Jumlah</th>
                                    <th>Aturan Pakai</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($visitor->prescriptions as $prescription)
                                    <tr>
                                        <td>{{ $prescription->medicine->nama_obat ?? '-' }}</td>
                                        <td>{{ $prescription->jumlah }}</td>
                                        <td>{{ $prescription->aturan_pakai ?? '-' }}</td>
                                        <td>{{ $prescription->keterangan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>

        <a href="{{ route('visitors.index') }}" class="btn btn-secondary mt-3">
            <i class="fas fa-arrow-left"></i> Kembali ke List
        </a>
    </div>
</div>
@endsection
