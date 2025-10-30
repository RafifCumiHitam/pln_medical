@extends('layouts.admin')

@section('title', 'Track Stok Obat - Create')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Input Data Log Obat</h1>
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Input Log Obat</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medicine-stocks.store') }}" id="medicineStockForm">
                @csrf

                {{-- Dropdown Pilih Obat --}}
                <div class="form-group">
                    <label>Medicine</label>
                    <div class="dropdown w-100">
                        <button class="btn btn-outline-secondary w-100 text-left d-flex justify-content-between align-items-center" 
                                type="button" id="dropdownMedicineBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span id="dropdownMedicineText">Pilih Obat</span>
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu w-100 p-2" aria-labelledby="dropdownMedicineBtn" style="max-height:250px;overflow-y:auto;">
                            <input type="text" class="form-control mb-2" id="dropdownMedicineSearch" placeholder="Cari Obat...">
                            <div id="dropdownMedicineOptions">
                                @foreach ($medicines as $medicine)
                                    @php
                                        // Ambil stok terakhir
                                        $latestStock = \App\Models\MedicineStock::where('medicine_id', $medicine->id)
                                            ->orderBy('id', 'desc')->first();
                                        $currentStock = $latestStock ? $latestStock->stok_akhir : 0;

                                        // Hitung status stok
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

                                        // Hitung status kadaluarsa
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
                                    data-status-stok="{{ $statusStok }}"
                                    data-stok-type="{{ $stokType }}"
                                    data-status-expired="{{ $statusExpired }}"
                                    data-expired-type="{{ $expiredType }}">
                                        {{ $medicine->nama_obat }} 
                                        (Stok: {{ $currentStock }}) — 
                                        <span class="font-weight-bold">{{ $statusStok }}</span>
                                        @if($statusExpired !== '✅ Aman (expired)')
                                            — <span class="text-muted">{{ $statusExpired }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="medicine_id" id="medicineSelect">
                </div>

                    {{-- Input jumlah masuk --}}
                <div class="form-group">
                    <label>Jumlah Stok Masuk</label>
                    <input type="number" name="jumlah_masuk" class="form-control" id="jumlahMasuk" min="0" value="0">
                    <div id="stockWarning" class="text-danger mt-1" style="display: none;"></div>
                </div>

                {{-- Input jumlah keluar --}}
                <div class="form-group">
                    <label>Jumlah Stok Keluar</label>
                    <input type="number" name="jumlah_keluar" class="form-control" min="0" value="0">
                </div>

                {{-- Input tanggal transaksi --}}
                <div class="form-group">
                    <label>Tanggal Transaksi</label>
                    <input type="date" name="tanggal_transaksi" class="form-control" required>
                </div>

                {{-- Input keterangan --}}
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"></textarea>
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
    const form = document.getElementById('medicineStockForm');
    const medicineInput = document.getElementById('medicineSelect');
    const jumlahMasuk = document.getElementById('jumlahMasuk');
    const jumlahKeluar = document.querySelector('input[name="jumlah_keluar"]');
    const tanggalTransaksi = document.querySelector('input[name="tanggal_transaksi"]');
    const stockWarning = document.getElementById('stockWarning');

    let stokTypeCurrent = null;
    let expiredTypeCurrent = null;
    let stokMinim = null;

    const medicineOptions = document.querySelectorAll('.medicine-option');
    const searchInput = document.getElementById('dropdownMedicineSearch');

    // Event select medicine
    medicineOptions.forEach(opt => {
        opt.addEventListener('click', function(e) {
            e.preventDefault();

            const label = this.dataset.label;
            stokTypeCurrent = this.dataset.stokType;
            expiredTypeCurrent = this.dataset.expiredType;
            stokMinim = parseInt(this.dataset.stokMinim, 10) || 0;
            const statusExpired = this.dataset.statusExpired;

            if (expiredTypeCurrent === 'expired') {
                Swal.fire({
                    icon: 'error',
                    title: 'Obat Kadaluarsa!',
                    html: `${label} tidak dapat dipilih karena sudah kadaluarsa`,
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            // ⚠️ Peringatan stok menipis/hampir kadaluarsa
            let alerts = [];
            if (stokTypeCurrent === 'low') alerts.push(`⚠️ Stok menipis (sisa minimal ${stokMinim})`);
            if (expiredTypeCurrent === 'near') alerts.push(`${statusExpired}`);
            if (alerts.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan Obat',
                    html: `${label}<br>${alerts.join('<br>')}`,
                    confirmButtonText: 'Lanjutkan'
                });
            }

            medicineInput.value = this.dataset.value;
            document.getElementById('dropdownMedicineText').innerHTML = `${label} — <span class="text-muted">${statusExpired}</span>`;
            stockWarning.style.display = 'none';
            validateStockInput();
        });
    });

    // Search filter
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        medicineOptions.forEach(opt => {
            if (opt.textContent.toLowerCase().includes(query)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    });

    // Validasi input stok masuk live
    jumlahMasuk.addEventListener('input', validateStockInput);

    // Validasi submit
    form.addEventListener('submit', function(e) {
        const masukValue = parseInt(jumlahMasuk.value, 10) || 0;
        const keluarValue = parseInt(jumlahKeluar.value, 10) || 0;
        const tanggalValue = document.querySelector('input[name="tanggal_transaksi"]').value;

        if (!medicineInput.value) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Harap pilih obat.' });
            return;
        }

        if ((stokTypeCurrent === 'empty' && expiredTypeCurrent !== 'expired') && masukValue <= 0) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Stok Habis', text: 'Harap isi jumlah stok masuk minimal 1.' });
            return;
        }

        if (jumlahMasuk.value === '' && stokTypeCurrent !== 'safe') {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Jumlah stok masuk tidak boleh kosong.' });
            return;
        }

        if (jumlahKeluar.value === '') {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Jumlah stok keluar tidak boleh kosong.' });
            return;
        }

        if (!tanggalValue) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Tanggal transaksi tidak boleh kosong.' });
            return;
        }
    });

    function validateStockInput() {
        const masukValue = parseInt(jumlahMasuk.value, 10) || 0;

        if (stokTypeCurrent === 'empty' && masukValue <= 0) {
            stockWarning.textContent = `Obat habis! Harap isi jumlah stok masuk minimal 1.`;
            stockWarning.style.display = 'block';
        } else if (stokMinim && masukValue > 0 && masukValue < stokMinim) {
            stockWarning.textContent = `Stok masuk tidak boleh kurang dari stok minim (${stokMinim}).`;
            stockWarning.style.display = 'block';
        } else {
            stockWarning.style.display = 'none';
        }
    }
});
</script>
@endpush
