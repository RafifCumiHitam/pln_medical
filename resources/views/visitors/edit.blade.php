@extends('layouts.admin')

@section('title', 'Track Visitor - Edit')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Edit Data Visitor</h1>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card shadow mb-4 border-left-primary">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Data Visitor</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('visitors.update', $visitor->id) }}" enctype="multipart/form-data" id="visitorForm">
            @csrf
            @method('PUT')

            {{-- Kategori --}}
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" id="kategori" class="form-control" onchange="toggleForm()">
                    <option value="karyawan" {{ $visitor->kategori === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    <option value="non_karyawan" {{ $visitor->kategori === 'non_karyawan' ? 'selected' : '' }}>Non-Karyawan</option>
                </select>
            </div>

            {{-- Karyawan Form --}}
            <div id="karyawanForm" style="display: {{ $visitor->kategori === 'karyawan' ? 'block' : 'none' }};">
                <div class="form-group">
                    <label>NID - Nama Karyawan</label>
                    <div class="dropdown w-100">
                        <button class="btn btn-outline-secondary w-100 text-left d-flex justify-content-between align-items-center"
                                type="button" id="dropdownNidBtn" data-toggle="dropdown">
                            <span id="dropdownNidText">
                                {{ $visitor->detail['nid'] ?? 'Pilih NID - Nama' }}{{ isset($visitor->detail['nama']) ? ' - ' . $visitor->detail['nama'] : '' }}
                            </span>
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu w-100 p-2" style="max-height:250px;overflow-y:auto;">
                            <input type="text" class="form-control mb-2" id="dropdownNidSearch" placeholder="Cari NID atau Nama...">
                            <div id="dropdownNidOptions">
                                @foreach ($karyawans as $karyawan)
                                    <a href="#" class="dropdown-item nid-option" 
                                       data-value="{{ $karyawan->nid }}" 
                                       data-nama="{{ $karyawan->nama_karyawan }}">
                                        {{ $karyawan->nid }} - {{ $karyawan->nama_karyawan }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="nid" id="nid" value="{{ $visitor->detail['nid'] ?? '' }}">
                    <input type="hidden" name="nama_karyawan" id="nama_karyawan_input" value="{{ $visitor->detail['nama'] ?? '' }}">
                </div>
            </div>

            {{-- Non-Karyawan Form --}}
            <div id="nonKaryawanForm" style="display: {{ $visitor->kategori === 'non_karyawan' ? 'block' : 'none' }};">
                <div class="form-group">
                    <label>Nama Non-Karyawan</label>
                    <input type="text" name="nama_non_karyawan" class="form-control" value="{{ $visitor->detail['nama'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Asal Perusahaan</label>
                    <input type="text" name="asal_perusahaan" class="form-control" value="{{ $visitor->detail['asal'] ?? '' }}">
                </div>
            </div>

            {{-- Tanggal --}}
            <div class="form-group">
                <label>Tanggal Kunjungan</label>
                <input type="date" name="tanggal_kunjungan" class="form-control" value="{{ $visitor->tanggal_kunjungan }}" required>
            </div>

            {{-- Pemeriksaan Fisik --}}
            <div class="form-group">
                <label>Cek Tensi (mmHg)</label>
                <input type="text" name="cek_tensi" class="form-control" value="{{ old('cek_tensi', $visitor->cek_tensi) }}" placeholder="Contoh: 120/80" required>
            </div>

            <div class="form-group">
                <label>Cek Heart Rate (bpm)</label>
                <input type="number" name="heart_rate" class="form-control" min="30" max="200" placeholder="Contoh: 75"
                       value="{{ old('heart_rate', $visitor->heart_rate ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Cek Respiratory Rate (kali/menit)</label>
                <input type="number" name="respiratory_rate" class="form-control" min="5" max="40" placeholder="Contoh: 18"
                       value="{{ old('respiratory_rate', $visitor->respiratory_rate ?? '') }}" required>
            </div>

            <div class="form-group">
                <label>Cek Suhu Badan (°C)</label>
                <input type="number" name="cek_suhu" class="form-control" step="0.1" min="30" max="45" 
                       value="{{ old('cek_suhu', $visitor->cek_suhu) }}" placeholder="Contoh: 36.5" required>
            </div>

            {{-- Keluhan, Diagnosis, Tindakan --}}
            <div class="form-group">
                <label>Keluhan</label>
                <textarea name="keluhan" class="form-control" required>{{ old('keluhan', $visitor->keluhan) }}</textarea>
            </div>
            <div class="form-group">
                <label>Diagnosis</label>
                <textarea name="diagnosis" class="form-control">{{ old('diagnosis', $visitor->diagnosis) }}</textarea>
            </div>
            <div class="form-group">
                <label>Tindakan</label>
                <textarea name="tindakan" class="form-control">{{ old('tindakan', $visitor->tindakan) }}</textarea>
            </div>

            {{-- Prescription Table --}}
            <div class="form-group">
                <label>Prescription for Patient (Optional)</label>
                <table class="table table-bordered table-hover" id="prescriptionTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Medicine</th>
                            <th>Jumlah</th>
                            <th>Aturan Pakai</th>
                            <th>Keterangan</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitor->prescriptions as $prescription)
                        @php
                            $medicine = $prescription->medicine;
                            $latestStock = $medicine->latestStock;
                            $currentStock = $latestStock ? $latestStock->stok_akhir : 0;
                            $expired = $medicine->expired_date ? \Carbon\Carbon::parse($medicine->expired_date) : null;
                            $now = \Carbon\Carbon::now();
                            $daysToExpire = $expired ? $now->diffInDays($expired, false) : null;
                            $status = '';

                            if ($expired && $expired->isPast()) {
                                $status = 'Kadaluarsa';
                            } elseif ($daysToExpire !== null && $daysToExpire <= 7) {
                                $status = 'Mendekati Kadaluarsa';
                            } elseif ($currentStock <= 0) {
                                $status = 'Stok Habis';
                            } elseif ($currentStock <= $medicine->stok_minim) {
                                $status = 'Stok Menipis';
                            } else {
                                $status = 'Tersedia';
                            }
                        @endphp
                        <tr class="prescription-row">
                            <td>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-secondary w-100 text-left d-flex justify-content-between align-items-center" 
                                            type="button" data-toggle="dropdown">
                                        <span class="dropdown-text">
                                            {{ $medicine->nama_obat }} - {{ $medicine->satuan }}
                                            ({{ $status }}, Stok: {{ $currentStock }})
                                        </span>
                                        <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu w-100 p-2" style="max-height:250px;overflow-y:auto;">
                                        <input type="text" class="form-control mb-2 medicine-search" placeholder="Cari Obat...">
                                        <div class="medicine-options">
                                            @foreach ($medicines as $medicine)
                                                @php
                                                    $latestStock = \App\Models\MedicineStock::where('medicine_id', $medicine->id)
                                                        ->orderBy('id','desc')->first();
                                                    $currentStock = $latestStock ? $latestStock->stok_akhir : 0;

                                                    if ($currentStock <= 0) { $statusStok='❌ Habis'; $stokType='empty'; }
                                                    elseif ($medicine->stok_minim !== null && $currentStock <= $medicine->stok_minim) { $statusStok='⚠️ Menipis'; $stokType='low'; }
                                                    else { $statusStok='✅ Aman (stok)'; $stokType='safe'; }

                                                    $expiredDate = $medicine->expired_date ? \Carbon\Carbon::parse($medicine->expired_date) : null;
                                                    $today = \Carbon\Carbon::today();
                                                    $statusExpired='✅ Aman (expired)'; $expiredType='safe';
                                                    if ($expiredDate && $expiredDate->isPast()) { $statusExpired='❌ Kadaluarsa ('.$expiredDate->format('d-m-Y').')'; $expiredType='expired'; }
                                                    elseif ($expiredDate && $expiredDate->diffInDays($today) <= 30) { $statusExpired='⚠️ Hampir Kadaluarsa ('.$expiredDate->format('d-m-Y').')'; $expiredType='near'; }
                                                @endphp
                                                <a href="#"
                                                   class="dropdown-item medicine-option"
                                                   data-value="{{ $medicine->id }}"
                                                   data-label="{{ $medicine->nama_obat }} (Stok: {{ $currentStock }})"
                                                   data-stok-minim="{{ $medicine->stok_minim }}"
                                                   data-stok-type="{{ $stokType }}"
                                                   data-status-stok="{{ $statusStok }}"
                                                   data-status-expired="{{ $statusExpired }}"
                                                   data-expired-type="{{ $expiredType }}">
                                                    {{ $medicine->nama_obat }}
                                                    (Stok: {{ $currentStock }}) — <span class="font-weight-bold">{{ $statusStok }}</span>
                                                    @if($statusExpired !== '✅ Aman (expired)') — <span class="text-muted">{{ $statusExpired }}</span>@endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="medicine_id[]" class="medicine-id" value="{{ $prescription->medicine_id }}">
                            </td>
                            <td><input type="number" name="jumlah_obat[]" class="form-control" min="1" value="{{ $prescription->jumlah }}"></td>
                            <td><input type="text" name="aturan_pakai[]" class="form-control" value="{{ $prescription->aturan_pakai }}"></td>
                            <td><textarea name="keterangan[]" class="form-control">{{ $prescription->keterangan }}</textarea></td>
                            <td class="text-center"><button type="button" class="btn btn-danger remove-prescription"><i class="fas fa-trash"></i></button></td>
                        </tr>
                        @empty
                        <tr class="prescription-row">
                            <td colspan="5" class="text-center">No prescriptions added</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary mt-2" id="add-prescription">Add Row</button>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('visitors.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    toggleForm();

    function toggleForm() {
        const kategori = document.getElementById('kategori').value;
        document.getElementById('karyawanForm').style.display = kategori === 'karyawan' ? 'block' : 'none';
        document.getElementById('nonKaryawanForm').style.display = kategori === 'non_karyawan' ? 'block' : 'none';
    }

    // Dropdown karyawan
    document.querySelectorAll('.nid-option').forEach(opt => {
        opt.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('nid').value = this.dataset.value;
            document.getElementById('nama_karyawan_input').value = this.dataset.nama;
            document.getElementById('dropdownNidText').textContent = `${this.dataset.value} - ${this.dataset.nama}`;
        });
    });

    // Prescription rows handling
    const addBtn = document.getElementById('add-prescription');
    const tableBody = document.querySelector('#prescriptionTable tbody');

    addBtn.addEventListener('click', function() {
        const template = document.querySelector('.prescription-row');
        const clone = template.cloneNode(true);
        clone.querySelectorAll('input, textarea').forEach(el => el.value = '');
        const dropdownText = clone.querySelector('.dropdown-text');
        if (dropdownText) dropdownText.textContent = 'Pilih Obat';
        clone.querySelectorAll('input[type="number"], input[type="text"], textarea, button.remove-prescription').forEach(el => el.style.display='none');
        tableBody.appendChild(clone);
        initRow(clone);
    });

    function initRow(row) {
        const optionsContainer = row.querySelector('.medicine-options');
        const options = optionsContainer ? optionsContainer.querySelectorAll('.medicine-option') : [];
        const dropdownTextEl = row.querySelector('.dropdown-text');
        const medIdInput = row.querySelector('.medicine-id');
        const jumlah = row.querySelector('input[name="jumlah_obat[]"]');
        const aturan = row.querySelector('input[name="aturan_pakai[]"]');
        const ket = row.querySelector('textarea[name="keterangan[]"]');
        const removeBtn = row.querySelector('.remove-prescription');
        const searchInput = row.querySelector('.medicine-search');

        options.forEach(opt => {
            opt.addEventListener('click', function(e) {
                e.preventDefault();
                const value = this.dataset.value;
                const label = this.dataset.label || this.textContent.trim();
                const stokType = this.dataset.stokType;
                const stokMinim = parseInt(this.dataset.stokMinim, 10) || 0;
                const statusExpired = this.dataset.statusExpired || '';
                const expiredType = this.dataset.expiredType;

                // ==== Cek duplikat obat ====
                const selectedIds = Array.from(document.querySelectorAll('.medicine-id')).map(i => i.value);
                if(selectedIds.includes(value) && medIdInput.value != value){
                    Swal.fire({ icon:'warning', title:'Obat Duplikat', html:`Obat ${label} sudah ada di resep ini.`, confirmButtonText:'OK' });
                    return;
                }

                if(expiredType==='expired'){
                    Swal.fire({ icon:'error', title:'Obat Kadaluarsa!', html:`${label} tidak dapat dipilih karena sudah kadaluarsa.`, confirmButtonText:'Mengerti'});
                    medIdInput.value=''; dropdownTextEl.textContent='Pilih Obat';
                    [jumlah, aturan, ket, removeBtn].forEach(el=>{if(el) el.style.display='none';});
                    return;
                }
                if(stokType==='empty'){
                    Swal.fire({ icon:'error', title:'Stok Habis', html:`${label} tidak dapat dipilih karena stok habis.`, confirmButtonText:'Mengerti'});
                    medIdInput.value=''; dropdownTextEl.textContent='Pilih Obat';
                    [jumlah, aturan, ket, removeBtn].forEach(el=>{if(el) el.style.display='none';});
                    return;
                }
                let alerts=[];
                if(stokType==='low') alerts.push(`⚠️ Stok menipis (minimal ${stokMinim})`);
                if(expiredType==='near') alerts.push(`${statusExpired}`);
                if(alerts.length>0){Swal.fire({icon:'warning', title:'Peringatan Obat', html:`${label}<br>${alerts.join('<br>')}`, confirmButtonText:'Lanjutkan'});}
                
                medIdInput.value=value;
                dropdownTextEl.innerHTML=`${label} — <span class="text-muted">${statusExpired}</span>`;
                [jumlah, aturan, ket, removeBtn].forEach(el=>{if(el) el.style.display='block';});
            });
        });

        if(searchInput){
            searchInput.addEventListener('input', function(){
                const q=this.value.toLowerCase();
                options.forEach(o=>{o.style.display=o.textContent.toLowerCase().includes(q)?'':'none';});
            });
        }

        if(removeBtn){
            removeBtn.addEventListener('click', function(){ row.remove(); });
        }
    }

    const initialRow = document.querySelector('.prescription-row');
    if(initialRow) initRow(initialRow);

    document.addEventListener('click', function(e){
        const toggle = e.target.closest('.btn[data-toggle="dropdown"]');
        if(toggle && toggle.closest('#prescriptionTable')){
            const row = toggle.closest('.prescription-row');
            if(row) initRow(row);
        }
    });
});
</script>
@endsection
