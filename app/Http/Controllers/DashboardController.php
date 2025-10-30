<?php

namespace App\Http\Controllers;

use App\Models\MedicineStock;
use App\Models\Visitor;
use App\Models\Medicine;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Halaman utama dashboard
     */
    public function index(Request $request)
    {
        $interval = (int) $request->get('interval', 30); // default 30 hari
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        // Tentukan rentang tanggal
        if ($customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
            $intervalLabel = 'Custom Range';
        } else {
            $endDate = now()->endOfDay();
            $startDate = now()->subDays($interval - 1)->startOfDay();
            $intervalLabel = "Last {$interval} Days";
        }

        // Ambil data kunjungan harian
        $visitorCounts = Visitor::selectRaw('DATE(tanggal_kunjungan) as date, COUNT(*) as count')
            ->whereBetween('tanggal_kunjungan', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = $this->generateDateLabels($startDate, $endDate);
        $data = array_map(fn($date) => $visitorCounts[$date] ?? 0, $labels);

        // Debug data chart
        Log::debug('Dashboard Chart Data', [
            'interval' => $intervalLabel,
            'labels' => $labels,
            'data' => $data,
        ]);

        // Data stok obat
        $medicineStocks = MedicineStock::with('medicine')
            ->latest()
            ->take(5)
            ->get();

        $medicineNames = $medicineStocks
            ->pluck('medicine.nama_obat')
            ->filter()
            ->values()
            ->toArray();

        $stockLevels = $medicineStocks
            ->pluck('stok_akhir')
            ->map(fn($stock) => $stock ?? 0)
            ->toArray();

        // Distribusi kategori obat (untuk Pie Chart)
        $categoryData = Medicine::selectRaw('kategori, COUNT(*) as count')
            ->groupBy('kategori')
            ->pluck('count', 'kategori')
            ->toArray();

        // Aktivitas terbaru
        $activityLogs = ActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'labels',
            'data',
            'interval',
            'intervalLabel',
            'medicineNames',
            'stockLevels',
            'categoryData',
            'activityLogs'
        ));
    }

    /**
     * Endpoint AJAX untuk update chart tanpa reload
     */
    public function getChartData(Request $request)
    {
        $interval = (int) $request->get('interval', 30);
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        if ($customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
        } else {
            $endDate = now()->endOfDay();
            $startDate = now()->subDays($interval - 1)->startOfDay();
        }

        $visitorCounts = Visitor::selectRaw('DATE(tanggal_kunjungan) as date, COUNT(*) as count')
            ->whereBetween('tanggal_kunjungan', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = $this->generateDateLabels($startDate, $endDate);
        $data = array_map(fn($date) => $visitorCounts[$date] ?? 0, $labels);

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Generate array tanggal untuk label chart
     */
    private function generateDateLabels($startDate, $endDate)
    {
        $labels = [];
        $current = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        while ($current->lte($end)) {
            $labels[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $labels;
    }
}
