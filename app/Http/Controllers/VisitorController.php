<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\Prescription;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\Karyawan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VisitorController extends Controller
{
    public function index()
    {
        $visitors = Visitor::with('user')->get();
        return view('visitors.index', compact('visitors'));
    }

    public function create()
    {
        // ==================== Revisi: fetch dari db2 ====================
        $karyawans = Karyawan::importFromDb2(); // Ambil data karyawan dari db2 sesuai mapping
        // ================================================================

        $medicines = Medicine::with(['latestStock'])->get()->map(function ($m) {
            $latest = $m->latestStock;
            $m->stok_akhir = $latest ? $latest->stok_akhir : 0;

            // ✅ expired_date langsung dari table medicines
            $expiredDate = $m->expired_date ? \Carbon\Carbon::parse($m->expired_date) : null;
            $today = \Carbon\Carbon::today();

            // === STATUS STOK ===
            if ($m->stok_akhir <= 0) {
                $m->status_stok = '❌ Habis';
                $m->stok_type = 'empty';
            } elseif ($m->stok_minim !== null && $m->stok_akhir <= $m->stok_minim) {
                $m->status_stok = '⚠️ Menipis';
                $m->stok_type = 'low';
            } else {
                $m->status_stok = '✅ Aman (stok)';
                $m->stok_type = 'safe';
            }

            // === STATUS EXPIRED ===
            if ($expiredDate && $expiredDate->isPast()) {
                $m->status_expired = '❌ Kadaluarsa (' . $expiredDate->format('d-m-Y') . ')';
                $m->expired_type = 'expired';
            } elseif ($expiredDate && $expiredDate->diffInDays($today) <= 30) {
                $m->status_expired = '⚠️ Hampir Kadaluarsa (' . $expiredDate->format('d-m-Y') . ')';
                $m->expired_type = 'near';
            } else {
                $m->status_expired = '✅ Aman (expired)';
                $m->expired_type = 'safe';
            }

            return $m;
        });

        return view('visitors.create', compact('karyawans', 'medicines'));
    }

    public function edit($id)
    {
        $visitor = Visitor::with('prescriptions.medicine.latestStock')->findOrFail($id);

        // ==================== Revisi: fetch dari db2 ====================
        $karyawans = Karyawan::importFromDb2(); // Ambil data karyawan dari db2 sesuai mapping
        // ================================================================

        $medicines = Medicine::with(['latestStock'])->get()->map(function ($m) {
            $latest = $m->latestStock;
            $m->stok_akhir = $latest ? $latest->stok_akhir : 0;
            $expiredDate = $m->expired_date ? \Carbon\Carbon::parse($m->expired_date) : null;
            $today = \Carbon\Carbon::today();

            // === STATUS STOK ===
            if ($m->stok_akhir <= 0) {
                $m->status_stok = '❌ Habis';
                $m->stok_type = 'empty';
            } elseif ($m->stok_minim !== null && $m->stok_akhir <= $m->stok_minim) {
                $m->status_stok = '⚠️ Menipis';
                $m->stok_type = 'low';
            } else {
                $m->status_stok = '✅ Aman';
                $m->stok_type = 'safe';
            }

            // === STATUS EXPIRED ===
            if ($expiredDate && $expiredDate->isPast()) {
                $m->status_expired = '❌ Kadaluarsa (' . $expiredDate->format('d-m-Y') . ')';
                $m->expired_type = 'expired';
            } elseif ($expiredDate && $expiredDate->diffInDays($today) <= 30) {
                $m->status_expired = '⚠️ Hampir Kadaluarsa (' . $expiredDate->format('d-m-Y') . ')';
                $m->expired_type = 'near';
            } else {
                $m->status_expired = '✅ Aman';
                $m->expired_type = 'safe';
            }

            return $m;
        });

        return view('visitors.edit', compact('visitor', 'karyawans', 'medicines'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateVisitor($request);

        // =================== Auto-sync/update karyawan ===================
        if ($request->kategori === 'karyawan') {
            $this->syncKaryawanDb2ToDb1($request->nid);
        }
        // ================================================================

        $visitorData = $this->prepareVisitorData($request, $validated);

        try {
            DB::beginTransaction();

            $visitor = Visitor::create($visitorData);

            // Simpan resep
            if (!empty($validated['medicine_id'])) {
                foreach ($validated['medicine_id'] as $index => $medicineId) {
                    if ($medicineId) {
                        $this->validateMedicineStockAndExpiry($medicineId, $validated['jumlah_obat'][$index] ?? 1);
                        $this->handleMedicines($visitor, $medicineId, $validated['jumlah_obat'][$index] ?? 1, $request, $index);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save visitor or medicines: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('visitors.index')->with('success', 'Visitor added successfully');
    }

    public function update(Request $request, $id)
    {
        $visitor = Visitor::findOrFail($id);
        $validated = $this->validateVisitor($request);

        // =================== Auto-sync/update karyawan ===================
        if ($request->kategori === 'karyawan') {
            $this->syncKaryawanDb2ToDb1($request->nid);
        }
        // ================================================================

        $visitorData = $this->prepareVisitorData($request, $validated, $visitor);

        try {
            DB::beginTransaction();

            $visitor->update($visitorData);

            // Update resep
            $visitor->prescriptions()->delete();
            if (!empty($validated['medicine_id'])) {
                foreach ($validated['medicine_id'] as $index => $medicineId) {
                    if ($medicineId) {
                        $this->validateMedicineStockAndExpiry($medicineId, $validated['jumlah_obat'][$index] ?? 1);
                        $this->handleMedicines($visitor, $medicineId, $validated['jumlah_obat'][$index] ?? 1, $request, $index);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update visitor or medicines: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('visitors.index')->with('success', 'Visitor updated successfully');
    }

    public function show($id)
    {
        $visitor = Visitor::with('prescriptions.medicine.latestStock')->findOrFail($id);
        return view('visitors.show', compact('visitor'));
    }

    public function getRiwayat($nid)
    {
        $riwayat = Visitor::where('detail->nid', $nid)
            ->orderBy('tanggal_kunjungan', 'desc')
            ->with(['prescriptions.medicine']) // ✅ tambahkan relasi resep & obat
            ->limit (10)
            ->get(['id', 'tanggal_kunjungan', 'keluhan', 'diagnosis']);

        // Format response agar mudah dipakai di JS
        $riwayatData = $riwayat->map(function ($r) {
            return [
                'tanggal_kunjungan' => $r->tanggal_kunjungan,
                'keluhan' => $r->keluhan,
                'diagnosis' => $r->diagnosis,
                'prescriptions' => $r->prescriptions->map(function ($p) {
                    return [
                        'nama_obat' => $p->medicine->nama_obat ?? '-',
                        'jumlah' => $p->jumlah ?? '-',
                        'aturan_pakai' => $p->aturan_pakai ?? '-',
                    ];
                }),
            ];
        });

        return response()->json($riwayatData);
    }


    public function destroy($id)
    {
        $visitor = Visitor::findOrFail($id);
        // Tidak perlu hapus file cek_ekg lagi
        $visitor->delete();
        return redirect()->route('visitors.index')->with('success', 'Visitor deleted successfully');
    }

    /**
     * Export XLSX menggunakan PhpSpreadsheet 5.x + ext-gd
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $from = $request->start_date;
        $to = $request->end_date;

        $visitors = Visitor::with(['prescriptions.medicine', 'user'])
                            ->whereBetween('tanggal_kunjungan', [$from, $to])
                            ->get();

        $maxObat = $visitors->map(fn($v) => $v->prescriptions->count())->max();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $header = [
            'ID', 'Kategori', 'Nama/NID', 'Asal Perusahaan', 'Tanggal Kunjungan',
            'Keluhan', 'Diagnosis', 'Tindakan', 'User',
            'Cek Tensi', 'Heart Rate', 'Respiratory Rate', 'Cek Suhu'
        ];

        for ($i = 1; $i <= $maxObat; $i++) {
            $header[] = "Obat $i";
            $header[] = "Jumlah $i";
            $header[] = "Aturan Pakai $i";
            $header[] = "Keterangan $i";
        }

        $sheet->fromArray($header, NULL, 'A1');

        // Data
        $rowNum = 2;
        foreach ($visitors as $v) {
            $row = [
                $v->id,
                $v->kategori,
                $v->kategori === 'karyawan' ? ($v->detail['nid'] ?? '') : ($v->detail['nama'] ?? ''),
                $v->kategori === 'non_karyawan' ? ($v->detail['asal'] ?? '') : '',
                $v->tanggal_kunjungan,
                $v->keluhan,
                $v->diagnosis ?? '',
                $v->tindakan ?? '',
                $v->user->nama_lengkap ?? '',
                $v->cek_tensi ?? '',
                $v->heart_rate ?? '',
                $v->respiratory_rate ?? '',
                $v->cek_suhu ?? '',
            ];

            $prescriptions = $v->prescriptions;
            for ($i = 0; $i < $maxObat; $i++) {
                if (isset($prescriptions[$i])) {
                    $presc = $prescriptions[$i];
                    $row[] = $presc->medicine->nama_obat ?? '';
                    $row[] = $presc->jumlah ?? '';
                    $row[] = $presc->aturan_pakai ?? '';
                    $row[] = $presc->keterangan ?? '';
                } else {
                    $row = array_merge($row, ['', '', '', '']);
                }
            }

            $sheet->fromArray($row, NULL, 'A' . $rowNum);
            $rowNum++;
        }

        $filename = 'visitors_' . now()->format('Ymd_His') . '.xlsx';

        // Kirim ke browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $filename .'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    // =============================================================
    // Validation & Data Preparation
    // =============================================================

    protected function validateVisitor(Request $request)
    {
        return $request->validate([
            'kategori' => 'required|in:karyawan,non_karyawan',
            // ✅ Revisi: nid hanya required jika kategori karyawan, dan bisa null jika non_karyawan
            'nid' => 'nullable|required_if:kategori,karyawan',
            'nama_karyawan' => 'nullable|required_if:kategori,karyawan|string',
            'nama_non_karyawan' => 'nullable|required_if:kategori,non_karyawan|string',
            'asal_perusahaan' => 'nullable|required_if:kategori,non_karyawan|string',
            'tanggal_kunjungan' => 'required|date',
            'keluhan' => 'required|string',
            'diagnosis' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'cek_tensi' => 'nullable|string|max:50',
            'heart_rate' => 'nullable|numeric|min:30|max:200',
            'respiratory_rate' => 'nullable|numeric|min:5|max:60',
            'cek_suhu' => 'nullable|numeric|min:30|max:45',
            // Hapus validasi cek_ekg (file)
            'medicine_id' => 'nullable|array',
            'medicine_id.*' => 'nullable|exists:medicines,id',
            'jumlah_obat' => 'nullable|array',
            'jumlah_obat.*' => 'nullable|integer|min:1',
            'aturan_pakai' => 'nullable|array',
            'aturan_pakai.*' => 'nullable|string',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string',
        ]);
    }

    protected function prepareVisitorData(Request $request, $validated, $visitor = null)
    {
        if ($request->kategori === 'karyawan') {
            $detail = [
                'nid' => $request->nid,
                'nama' => $request->nama_karyawan,
            ];
        } else {
            $detail = [
                'nama' => $request->nama_non_karyawan,
                'asal' => $request->asal_perusahaan,
            ];
        }

        return [
            'kategori' => $validated['kategori'],
            'detail' => $detail,
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'keluhan' => $validated['keluhan'],
            'diagnosis' => $validated['diagnosis'] ?? null,
            'tindakan' => $validated['tindakan'] ?? null,
            'cek_tensi' => $validated['cek_tensi'] ?? null,
            'heart_rate' => $validated['heart_rate'] ?? null,
            'respiratory_rate' => $validated['respiratory_rate'] ?? null,
            'cek_suhu' => $validated['cek_suhu'] ?? null,
            'user_id' => auth()->id() ?? ($visitor ? $visitor->user_id : null),
        ];
    }

    // Logika Obat dan Stok
    protected function validateMedicineStockAndExpiry($medicineId, $jumlah)
    {
        $medicine = Medicine::findOrFail($medicineId);
        $latestStock = $medicine->latestStock;
        $currentStock = $latestStock ? $latestStock->stok_akhir : 0;
        $expiredDate = $latestStock ? $latestStock->expired_date : null;

        if ($currentStock < $jumlah) {
            throw new \Exception("Stok obat {$medicine->nama_obat} tidak mencukupi (tersisa: $currentStock)");
        }

        if ($expiredDate && now()->greaterThanOrEqualTo($expiredDate)) {
            throw new \Exception("Obat {$medicine->nama_obat} sudah kadaluarsa ($expiredDate)");
        }
    }

    protected function handleMedicines(Visitor $visitor, $medicineId, $jumlah, Request $request, $index)
    {
        $prescription = Prescription::where('visitor_id', $visitor->id)
            ->where('medicine_id', $medicineId)
            ->first();

        $oldJumlah = $prescription ? $prescription->jumlah : 0;
        $diffJumlah = $jumlah - $oldJumlah;

        $latestStock = MedicineStock::where('medicine_id', $medicineId)
            ->orderBy('id', 'desc')
            ->first();

        $currentStock = $latestStock ? $latestStock->stok_akhir : 0;
        $newStock = $currentStock - $diffJumlah;

        if ($prescription) {
            $prescription->update([
                'jumlah' => $jumlah,
                'aturan_pakai' => $request->input('aturan_pakai')[$index] ?? 'Standard',
                'keterangan' => $request->input('keterangan')[$index] ?? '',
            ]);
        } else {
            Prescription::create([
                'visitor_id' => $visitor->id,
                'medicine_id' => $medicineId,
                'jumlah' => $jumlah,
                'aturan_pakai' => $request->input('aturan_pakai')[$index] ?? 'Standard',
                'keterangan' => $request->input('keterangan')[$index] ?? '',
            ]);
        }

        // Update stok dan log
        $stockData = [
            'medicine_id' => $medicineId,
            'jumlah_masuk' => $diffJumlah < 0 ? abs($diffJumlah) : 0,
            'jumlah_keluar' => $diffJumlah > 0 ? $diffJumlah : 0,
            'stok_akhir' => $newStock,
            'tanggal_transaksi' => $visitor->tanggal_kunjungan,
            'keterangan' => ($diffJumlah > 0 ? 'Dispensed for visitor ' : 'Returned from visitor ') . $visitor->id,
            'user_id' => auth()->id() ?? null,
        ];

        MedicineStock::create($stockData);

        $activityType = $diffJumlah > 0 ? 'dispense' : ($diffJumlah < 0 ? 'return' : 'no_change');
        ActivityLog::create([
            'user_id' => auth()->id() ?? null,
            'activity_type' => $activityType,
            'table_name' => 'medicine_stocks',
            'record_id' => $medicineId,
            'new_data' => json_encode($stockData),
        ]);
    }

    public function syncKaryawanDb2ToDb1($nid)
    {
        // Ambil data karyawan dari DB2
        $karyawanDb2 = DB::connection('db2')->table('users as u')
            ->join('profiles as p', 'u.nid', '=', 'p.nid')
            ->where('u.nid', $nid)
            ->select('u.nid', 'u.name', 'p.gender', 'p.tahun_lahir', 'p.telp')
            ->first();

        if (!$karyawanDb2) {
            return null;
        }

        // Mapping gender
        $jk = strtoupper($karyawanDb2->gender);
        if (in_array($jk, ['L', 'LAKI', 'LAKI-LAKI'])) {
            $jk = 'L';
        } elseif (in_array($jk, ['P', 'PEREMPUAN'])) {
            $jk = 'P';
        } else {
            $jk = null;
        }

        // === Revisi: format tanggal lahir ===
        $tahunLahir = $karyawanDb2->tahun_lahir;
        if (preg_match('/^\d{4}$/', $tahunLahir)) {
            $tanggalLahir = $tahunLahir . '-01-01';
        } else {
            $tanggalLahir = date('Y-m-d', strtotime($tahunLahir));
        }

        // Cek apakah karyawan sudah ada di DB1
        $karyawan = Karyawan::where('nid', $karyawanDb2->nid)->first();

        if ($karyawan) {
            // Update data
            $karyawan->update([
                'nama_karyawan' => $karyawanDb2->name,
                'jenis_kelamin' => $jk,
                'tanggal_lahir' => $tanggalLahir,
                'no_telepon' => $karyawanDb2->telp,
                // no_rm tetap sama karena sudah ada
            ]);
        } else {
            // Generate no_rm unik (misal RM + timestamp + random)
            $no_rm = 'RM' . time() . rand(100, 999);

            // Insert data baru
            $karyawan = Karyawan::create([
                'nid' => $karyawanDb2->nid,
                'nama_karyawan' => $karyawanDb2->name,
                'jenis_kelamin' => $jk,
                'tanggal_lahir' => $tanggalLahir,
                'no_telepon' => $karyawanDb2->telp,
                'no_rm' => $no_rm,
            ]);
        }

        return $karyawan;
    }
}
