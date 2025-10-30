@extends('layouts.admin')

@section('title', 'Track Visitor - Create')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Input Data Visitor</h1>

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
        <h6 class="m-0 font-weight-bold text-primary">Form Visitor</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('visitors.store') }}" enctype="multipart/form-data" id="visitorForm">
            @csrf

            {{-- Kategori --}}
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" id="kategori" class="form-control" onchange="toggleForm()">
                    <option value="karyawan" selected>Karyawan</option>
                    <option value="non_karyawan">Non-Karyawan</option>
                </select>
            </div>

            {{-- Karyawan --}}
            <div id="karyawanForm">
                <div class="form-group">
                    <label>NID - Nama Karyawan</label>
                    <div class="dropdown w-100">
                        <button class="btn btn-outline-secondary w-100 text-left d-flex justify-content-between align-items-center"
                                type="button" id="dropdownNidBtn" data-toggle="dropdown">
                            <span id="dropdownNidText">Pilih NID - Nama</span>
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
                    <input type="hidden" name="nid" id="nid">
                    <input type="hidden" name="nama_karyawan" id="nama_karyawan_input">
                </div>
            </div>

            {{-- Non-Karyawan --}}
            <div id="nonKaryawanForm" style="display:none;">
                <div class="form-group">
                    <label>Nama Non-Karyawan</label>
                    <input type="text" name="nama_non_karyawan" class="form-control">
                </div>
                <div class="form-group">
                    <label>Asal Perusahaan</label>
                    <input type="text" name="asal_perusahaan" class="form-control">
                </div>
            </div>

            {{-- Pemeriksaan --}}
            <div class="form-group">
                <label>Cek Tensi (mmHg)</label>
                <input type="text" name="cek_tensi" class="form-control" placeholder="Contoh: 120/80" required>
            </div>

            <div class="form-group">
                <label>Cek Heart Rate (bpm)</label>
                <input type="number" name="heart_rate" class="form-control" min="30" max="200" placeholder="Contoh: 75" required>
            </div>

            <div class="form-group">
                <label>Cek Respiratory Rate (kali/menit)</label>
                <input type="number" name="respiratory_rate" class="form-control" min="5" max="40" placeholder="Contoh: 18" required>
            </div>

            <div class="form-group">
                <label>Cek Suhu Badan (°C)</label>
                <input type="number" name="cek_suhu" class="form-control" step="0.1" min="30" max="45" placeholder="Contoh: 36.5" required>
            </div>

            {{-- Keluhan --}}
            <div class="form-group">
                <label>Keluhan</label>
                <textarea name="keluhan" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label>Diagnosis</label>
                <textarea name="diagnosis" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label>Tindakan</label>
                <textarea name="tindakan" class="form-control"></textarea>
            </div>

            {{-- Resep Obat --}}
            <div class="form-group">
                <label>Resep Obat (Opsional)</label>
                <table class="table table-bordered table-hover" id="prescriptionTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Obat</th>
                            <th>Jumlah</th>
                            <th>Aturan Pakai</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="prescription-row">
                            <td>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-secondary w-100 text-left d-flex justify-content-between align-items-center"
                                            type="button" data-toggle="dropdown">
                                        <span class="dropdown-text">Pilih Obat</span>
                                        <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu w-100 p-2" style="max-height:250px;overflow-y:auto;">
                                        <input type="text" class="form-control mb-2 medicine-search" placeholder="Cari Obat...">
                                        <div class="medicine-options">
                                            @foreach ($medicines as $medicine)
                                                @php
                                                    $latestStock = \App\Models\MedicineStock::where('medicine_id', $medicine->id)
                                                        ->orderBy('id', 'desc')->first();
                                                    $currentStock = $latestStock ? $latestStock->stok_akhir : 0;

                                                    if ($currentStock <= 0) {
                                                        $statusStok = '❌ Habis';
                                                        $stokType = 'empty';
                                                    } elseif ($medicine->stok_minim !== null && $currentStock <= $medicine->stok_minim) {
                                                        $statusStok = '⚠️ Menipis';
                                                        $stokType = 'low';
                                                    } else {
                                                        $statusStok = '✅ Aman (stok)';
                                                        $stokType = 'safe';
                                                    }

                                                    $expiredDate = $medicine->expired_date ? \Carbon\Carbon::parse($medicine->expired_date) : null;
                                                    $today = \Carbon\Carbon::today();
                                                    $statusExpired = '✅ Aman (expired)';
                                                    $expiredType = 'safe';
                                                    if ($expiredDate && $expiredDate->isPast()) {
                                                        $statusExpired = '❌ Kadaluarsa (' . $expiredDate->format('d-m-Y') . ')';
                                                        $expiredType = 'expired';
                                                    } elseif ($expiredDate && $expiredDate->diffInDays($today) <= 30) {
                                                        $statusExpired = '⚠️ Hampir Kadaluarsa (' . $expiredDate->format('d-m-Y') . ')';
                                                        $expiredType = 'near';
                                                    }
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
                                                    @if($statusExpired !== '✅ Aman (expired)')
                                                        — <span class="text-muted">{{ $statusExpired }}</span>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="medicine_id[]" class="medicine-id">
                            </td>
                            <td><input type="number" name="jumlah_obat[]" class="form-control" min="1" style="display:none;"></td>
                            <td><input type="text" name="aturan_pakai[]" class="form-control" style="display:none;"></td>
                            <td><textarea name="keterangan[]" class="form-control" style="display:none;"></textarea></td>
                            <td class="text-center"><button type="button" class="btn btn-danger remove-prescription" style="display:none;">Hapus</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary mt-2" id="add-prescription">Tambah Obat</button>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    toggleForm();

    function toggleForm() {
        const kategori = document.getElementById('kategori').value;
        document.getElementById('karyawanForm').style.display = kategori === 'karyawan' ? 'block' : 'none';
        document.getElementById('nonKaryawanForm').style.display = kategori === 'non_karyawan' ? 'block' : 'none';
    }

    // ===================== NID Dropdown & Search =====================
    const dropdownNidBtn = document.getElementById('dropdownNidBtn');
    const dropdownNidText = document.getElementById('dropdownNidText');
    const nidInput = document.getElementById('nid');
    const namaInput = document.getElementById('nama_karyawan_input');
    const nidSearchInput = document.getElementById('dropdownNidSearch');
    const nidOptionsContainer = document.getElementById('dropdownNidOptions');
    const nidOptions = Array.from(nidOptionsContainer.querySelectorAll('.nid-option'));

    nidOptions.forEach(opt => {
        opt.addEventListener('click', function(e) {
            e.preventDefault();
            nidInput.value = this.dataset.value;
            namaInput.value = this.dataset.nama;
            dropdownNidText.textContent = `${this.dataset.value} - ${this.dataset.nama}`;
        });
    });

    if (nidSearchInput) {
        nidSearchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            nidOptions.forEach(opt => {
                opt.style.display = opt.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    if (dropdownNidBtn) {
        dropdownNidBtn.addEventListener('click', function() {
            nidSearchInput.value = '';
            nidOptions.forEach(opt => opt.style.display = '');
        });
    }

    // ===================== Prescription Table =====================
    const addBtn = document.getElementById('add-prescription');
    const tableBody = document.querySelector('#prescriptionTable tbody');

    addBtn.addEventListener('click', function() {
        const template = document.querySelector('.prescription-row');
        const clone = template.cloneNode(true);

        clone.querySelectorAll('input, textarea').forEach(el => { el.value = ''; });
        clone.querySelectorAll('input[type="number"], input[type="text"], textarea, button.remove-prescription').forEach(el => { el.style.display = 'none'; });
        const dropdownText = clone.querySelector('.dropdown-text');
        if (dropdownText) dropdownText.textContent = 'Pilih Obat';

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

                // ======= Cek duplicate obat di row lain =======
                const allMedIds = Array.from(document.querySelectorAll('.medicine-id')).map(i=>i.value).filter(v=>v);
                if(allMedIds.includes(value) && medIdInput.value !== value){
                    Swal.fire({icon:'warning', title:'Duplikasi Obat', html:`${label} sudah dipilih di row lain.`, confirmButtonText:'Mengerti'});
                    return;
                }

                if (expiredType === 'expired') {
                    Swal.fire({ icon:'error', title:'Obat Kadaluarsa!', html:`${label} tidak dapat dipilih karena sudah kadaluarsa.`, confirmButtonText:'Mengerti'});
                    medIdInput.value = '';
                    dropdownTextEl.textContent = 'Pilih Obat';
                    [jumlah, aturan, ket, removeBtn].forEach(el=>{if(el) el.style.display='none';});
                    return;
                }

                if (stokType === 'empty') {
                    Swal.fire({ icon:'error', title:'Stok Habis', html:`${label} tidak dapat dipilih karena stok habis.`, confirmButtonText:'Mengerti'});
                    medIdInput.value = '';
                    dropdownTextEl.textContent = 'Pilih Obat';
                    [jumlah, aturan, ket, removeBtn].forEach(el=>{if(el) el.style.display='none';});
                    return;
                }

                let alerts=[];
                if(stokType==='low') alerts.push(`⚠️ Stok menipis (minimal ${stokMinim})`);
                if(expiredType==='near') alerts.push(`${statusExpired}`);
                if(alerts.length>0){Swal.fire({icon:'warning', title:'Peringatan Obat', html:`${label}<br>${alerts.join('<br>')}`, confirmButtonText:'Lanjutkan'});}

                medIdInput.value = value;
                dropdownTextEl.innerHTML = `${label} — <span class="text-muted">${statusExpired}</span>`;
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
            if(row){ initRow(row); }
        }
    });
});
</script>
@endpush
