<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    /**
     * Tampilkan form input obat
     */
    public function create()
    {
        // Menampilkan file resources/views/medicine-stocks/input-medicine.blade.php
        return view('medicine-stocks.input-medicine');
    }

    /**
     * Simpan data obat ke database
     */
    public function store(Request $request)
    {
        // Validasi data
        $validated = $request->validate([
            'nama_obat'   => 'required|string|max:255',
            'kode_obat'   => 'required|string|max:255|unique:medicines,kode_obat',
            'kategori'    => 'required|string|max:100',
            'satuan'      => 'required|string|max:100',
            'stok_minim'  => 'required|integer|min:0',
            'keterangan'  => 'nullable|string',
            'expired_date'=> 'required|date|after_or_equal:today',
        ]);

        // Simpan data obat ke database
        Medicine::create($validated);

        // Redirect ke halaman stok obat setelah sukses
        return redirect()->route('medicine-stocks.index')
            ->with('success', 'Data obat berhasil ditambahkan.');
    }
}