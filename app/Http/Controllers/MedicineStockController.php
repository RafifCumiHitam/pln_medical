<?php

namespace App\Http\Controllers;

use App\Models\MedicineStock;
use App\Models\Medicine;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MedicineStockController extends Controller
{
    /**
     * INDEX — tampilkan stok terakhir tiap obat
     */
    public function index()
    {
        $medicines = Medicine::with(['latestStock'])->get(); // ambil stok terakhir

        foreach ($medicines as $med) {
            $lastStock = $med->latestStock;

            $med->stok_akhir = $lastStock ? $lastStock->stok_akhir : 0;

            // Tentukan status stok
            if ($med->stok_akhir <= 0) {
                $med->status_stok = 'habis';
            } elseif ($med->stok_minim !== null && $med->stok_akhir <= $med->stok_minim) {
                $med->status_stok = 'menipis';
            } else {
                $med->status_stok = 'aman';
            }

            // Ambil expired_date dari stok terakhir
            $expiredDate = $lastStock ? $lastStock->expired_date : $med->expired_date; // fallback ke medicines.expired_date
            if ($expiredDate) {
                $expired = Carbon::parse($expiredDate);
                $med->kadaluarsa = $expired->format('Y-m-d'); // tampilkan di tabel
                $med->isExpired = $expired->isPast();
                $med->isAlmostExpired = !$expired->isPast() && $expired->diffInDays(now()) <= 30;
            } else {
                $med->kadaluarsa = null;
                $med->isExpired = false;
                $med->isAlmostExpired = false;
            }
        }

        return view('medicine-stocks.index', compact('medicines'));
    }

    /**
     * LOG — tampilkan seluruh transaksi stok
     */
    public function logs()
    {
        $stocks = MedicineStock::with(['medicine', 'user'])
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();

        return view('medicine-stocks.logs', compact('stocks'));
    }

    /**
     * CREATE — form tambah stok obat
     */
    public function create()
    {
        $medicines = Medicine::all();
        return view('medicine-stocks.create', compact('medicines'));
    }

    /**
     * STORE — simpan stok baru
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'jumlah_masuk' => 'nullable|integer|min:0',
            'jumlah_keluar' => 'nullable|integer|min:0',
            'tanggal_transaksi' => 'required|date',
            'expired_date' => 'nullable|date|after_or_equal:today',
            'keterangan' => 'nullable|string',
        ]);

        $lastStock = MedicineStock::where('medicine_id', $data['medicine_id'])
            ->orderBy('tanggal_transaksi', 'desc')
            ->first();

        $currentStock = $lastStock ? $lastStock->stok_akhir : 0;
        $newStock = $currentStock + ($data['jumlah_masuk'] ?? 0) - ($data['jumlah_keluar'] ?? 0);

        // Validasi stok negatif
        if ($newStock < 0) {
            return back()->withErrors(['error' => 'Stok obat tidak mencukupi!']);
        }

        // Simpan data stok baru
        $data['stok_akhir'] = $newStock;
        $data['user_id'] = Auth::id();

        try {
            $stock = MedicineStock::create($data);

            // Update expired_date di tabel medicines jika disertakan
            if (!empty($data['expired_date'])) {
                $medicine = Medicine::find($data['medicine_id']);
                $medicine->update(['expired_date' => $data['expired_date']]);
            }

            // Log aktivitas
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'create',
                'table_name' => 'medicine_stocks',
                'record_id' => $stock->id,
                'new_data' => json_encode($data),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create medicine stock: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan stok.']);
        }

        return redirect()->route('medicine-stocks.index')->with('success', 'Stok berhasil diperbarui.');
    }

    /**
     * SHOW — tampilkan detail stok
     */
    public function show($id)
    {
        $stock = MedicineStock::with(['medicine', 'user'])->findOrFail($id);
        return view('medicine-stocks.show', compact('stock'));
    }

    /**
     * EDIT — form edit stok
     */
    public function edit($id)
    {
        $stock = MedicineStock::with('medicine')->findOrFail($id);
        $medicines = Medicine::all();
        return view('medicine-stocks.edit', compact('stock', 'medicines'));
    }

    /**
     * UPDATE — ubah stok
     */
    public function update(Request $request, $id)
    {
        $stock = MedicineStock::findOrFail($id);
        $oldData = $stock->toArray();

        $data = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'jumlah_masuk' => 'nullable|integer|min:0',
            'jumlah_keluar' => 'nullable|integer|min:0',
            'tanggal_transaksi' => 'required|date',
            'expired_date' => 'nullable|date|after_or_equal:today',
            'keterangan' => 'nullable|string',
        ]);

        $latestStock = MedicineStock::where('medicine_id', $data['medicine_id'])
            ->orderBy('tanggal_transaksi', 'desc')
            ->first();

        $currentStock = $latestStock ? $latestStock->stok_akhir : 0;
        $diffMasuk = ($data['jumlah_masuk'] ?? 0) - ($stock->jumlah_masuk ?? 0);
        $diffKeluar = ($data['jumlah_keluar'] ?? 0) - ($stock->jumlah_keluar ?? 0);
        $netChange = $diffMasuk - $diffKeluar;
        $newStock = $currentStock + $netChange;

        if ($newStock < 0) {
            return back()->withErrors(['error' => 'Stok obat tidak mencukupi!']);
        }

        $data['stok_akhir'] = $newStock;
        $data['user_id'] = Auth::id();

        try {
            $stock->update($data);

            // Update tanggal kadaluarsa di tabel medicine jika disertakan
            if (!empty($data['expired_date'])) {
                $medicine = Medicine::find($data['medicine_id']);
                $medicine->update(['expired_date' => $data['expired_date']]);
            }

            $activityType = $netChange > 0 ? 'increase' : ($netChange < 0 ? 'decrease' : 'no_change');
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => $activityType,
                'table_name' => 'medicine_stocks',
                'record_id' => $stock->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update medicine stock: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal mengupdate stok.']);
        }

        return redirect()->route('medicine-stocks.index')->with('success', 'Stok berhasil diperbarui.');
    }

    /**
     * DESTROY — hapus stok
     */
    public function destroy($id)
    {
        $stock = MedicineStock::findOrFail($id);
        $oldData = $stock->toArray();

        try {
            $stock->delete();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'delete',
                'table_name' => 'medicine_stocks',
                'record_id' => $id,
                'old_data' => json_encode($oldData),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete medicine stock: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menghapus stok.']);
        }

        return redirect()->route('medicine-stocks.index')->with('success', 'Stok berhasil dihapus.');
    }

    /**
     * CREATE MEDICINE — form tambah obat
     */
    public function createMedicine()
    {
        return view('medicine-stocks.input-medicine');
    }

    /**
     * STORE MEDICINE — simpan obat baru
     */
    public function storeMedicine(Request $request)
    {
        $data = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kode_obat' => 'required|string|max:50',
            'kategori' => 'required|in:Pil,Salep,Sirup,Injeksi,Lainnya',
            'satuan' => 'required|in:tablet,salep,ml,tube,vial,lainnya',
            'stok_minim' => 'required|integer|min:0',
            'expired_date' => 'required|date',
        ]);

        try {
            $medicine = Medicine::create($data);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'create',
                'table_name' => 'medicines',
                'record_id' => $medicine->id,
                'new_data' => json_encode($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create medicine: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan obat.']);
        }

        return redirect()->route('medicine-stocks.index')->with('success', 'Obat berhasil ditambahkan.');
    }

    /**
     * EDIT MEDICINE — form edit obat
     */
    public function editMedicine($id)
    {
        $medicine = Medicine::findOrFail($id);
        return view('medicine-stocks.edit-medicine', compact('medicine'));
    }

    /**
     * UPDATE MEDICINE — ubah data obat
     */
    public function updateMedicine(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);
        $oldData = $medicine->toArray();

        $data = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kode_obat' => 'required|string|max:50',
            'kategori' => 'required|in:Pil,Salep,Sirup,Injeksi,Lainnya',
            'satuan' => 'required|in:tablet,salep,ml,tube,vial,lainnya',
            'stok_minim' => 'required|integer|min:0',
            'expired_date' => 'required|date',
        ]);

        try {
            $medicine->update($data);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'update',
                'table_name' => 'medicines',
                'record_id' => $medicine->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update medicine: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal mengupdate obat.']);
        }

        return redirect()->route('medicine-stocks.index')->with('success', 'Obat berhasil diperbarui.');
    }

    /**
     * DESTROY MEDICINE — hapus obat
     */
    public function destroyMedicine($id)
    {
        $medicine = Medicine::findOrFail($id);
        $oldData = $medicine->toArray();

        // Cek apakah ada stok terkait
        if (MedicineStock::where('medicine_id', $id)->exists()) {
            return back()->withErrors(['error' => 'Obat tidak dapat dihapus karena memiliki riwayat stok.']);
        }

        try {
            $medicine->delete();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity_type' => 'delete',
                'table_name' => 'medicines',
                'record_id' => $id,
                'old_data' => json_encode($oldData),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete medicine: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menghapus obat.']);
        }

        return redirect()->route('medicine-stocks.index')->with('success', 'Obat berhasil dihapus.');
    }

    /**
     * AJAX — cek stok & status obat terbaru
     */
    public function checkStock($id)
    {
        $medicine = Medicine::findOrFail($id);

        // Ambil stok terakhir
        $lastStock = MedicineStock::where('medicine_id', $medicine->id)
            ->orderBy('tanggal_transaksi', 'desc')
            ->first();

        $stok_akhir = $lastStock ? $lastStock->stok_akhir : 0;

        // Tentukan status stok
        if ($stok_akhir <= 0) {
            $status = 'empty';
            $status_text = '⚠️ Stok habis';
        } elseif ($medicine->stok_minim !== null && $stok_akhir <= $medicine->stok_minim) {
            $status = 'low';
            $status_text = '⚠️ Stok menipis';
        } else {
            $status = 'safe';
            $status_text = '✅ Stok aman';
        }

        // Cek kadaluarsa
        $expiredType = 'safe';
        $statusExpired = '✅ Aman';
        if ($medicine->expired_date) {
            $expiredDate = Carbon::parse($medicine->expired_date);
            if ($expiredDate->isPast()) {
                $expiredType = 'expired';
                $statusExpired = '❌ Kadaluarsa';
                $status = 'expired';
                $status_text = '❌ Obat kadaluarsa';
            } elseif ($expiredDate->diffInDays(Carbon::today()) <= 30) {
                $expiredType = 'near';
                $statusExpired = '⚠️ Hampir kadaluarsa';
                $status_text .= ' ⚠️ Hampir kadaluarsa';
            }
        }

        return response()->json([
            'id' => $medicine->id,
            'nama_obat' => $medicine->nama_obat,
            'stok_akhir' => $stok_akhir,
            'status' => $status, // empty, low, safe, expired
            'status_text' => $status_text,
            'expired_type' => $expiredType,
            'status_expired' => $statusExpired,
        ]);
    }

    /**
     * EXPORT — ekspor rekap rata-rata pengeluaran obat ke XLSX
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $stocks = MedicineStock::with(['medicine', 'user'])
            ->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date])
            ->orderBy('tanggal_transaksi', 'asc')
            ->get();

        if ($stocks->isEmpty()) {
            return back()->withErrors(['error' => 'Tidak ada data transaksi pada rentang tanggal tersebut.']);
        }

        $avgKeluar = round($stocks->avg('jumlah_keluar'), 2);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->fromArray([
            'ID', 'Nama Obat', 'Jumlah Masuk', 'Jumlah Keluar', 'Stok Akhir', 'Keterangan', 'Tanggal Transaksi', 'User'
        ], NULL, 'A1');

        // Data
        $rowNum = 2;
        foreach ($stocks as $stock) {
            $sheet->setCellValue("A{$rowNum}", $stock->id);
            $sheet->setCellValue("B{$rowNum}", $stock->medicine->nama_obat ?? 'N/A');
            $sheet->setCellValue("C{$rowNum}", $stock->jumlah_masuk ?? 0);
            $sheet->setCellValue("D{$rowNum}", $stock->jumlah_keluar ?? 0);
            $sheet->setCellValue("E{$rowNum}", $stock->stok_akhir ?? 0);
            $sheet->setCellValue("F{$rowNum}", $stock->keterangan ?? '-');
            $sheet->setCellValue("G{$rowNum}", $stock->tanggal_transaksi);
            $sheet->setCellValue("H{$rowNum}", $stock->user->name ?? '-');
            $rowNum++;
        }

        // Baris rata-rata
        $sheet->setCellValue("A{$rowNum}", 'Rata-rata Pengeluaran');
        $sheet->setCellValue("B{$rowNum}", $avgKeluar);

        $writer = new Xlsx($spreadsheet);
        $filename = "rekap_pengeluaran_obat_{$request->start_date}_to_{$request->end_date}.xlsx";

        // Return response XLSX
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
