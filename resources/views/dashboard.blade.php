@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Visitors</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ App\Models\Visitor::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Medicines</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ App\Models\Medicine::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $lowStockCount = App\Models\Medicine::whereHas('stocks', function($q) {
                                        $q->whereColumn('stok_akhir', '<', 'stok_minim');
                                    })->count();
                                @endphp
                                {{ $lowStockCount }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- New Card: Expired Medicines -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expired / Near Expiry</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ App\Models\Medicine::whereDate('expired_date', '<=', now()->addDays(30))->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Rekap Kunjungan Harian</h6>

                    <!-- Interval Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="intervalDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $intervalLabel ?? 'Last 30 Days' }}
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="intervalDropdown">
                            <li><a class="dropdown-item" href="#" data-interval="7">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#" data-interval="30">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#" data-interval="90">Last 90 Days</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div class="chart-area">
                        @if (count($labels) > 0)
                            <canvas id="visitorAreaChart"></canvas>
                        @else
                            <p class="text-center text-gray-600">No visitor data available for the selected period.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar & Pie Charts -->
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stock Levels by Medicine - Bar Chart</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        @if (count($medicineNames) > 0)
                            <canvas id="stockBarChart"></canvas>
                        @else
                            <p class="text-center text-gray-600">No stock data available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Medicine Category Distribution - Pie Chart</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-end">
                    <div class="chart-pie pt-4 pb-2 flex-grow-1">
                        @if (count($categoryData) > 0)
                            <canvas id="categoryPieChart"></canvas>
                        @else
                            <p class="text-center text-gray-600">No category data available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Table</th>
                                    <th>Record ID</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activityLogs as $log)
                                    <tr>
                                        <td>{{ $log->user->nama_lengkap ?? 'N/A' }}</td>
                                        <td>{{ $log->activity_type }}</td>
                                        <td>{{ $log->table_name }}</td>
                                        <td>{{ $log->record_id }}</td>
                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No recent activities.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // === AREA CHART (AJAX Dynamic) ===
    const intervalButton = document.getElementById('intervalDropdown');
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    const ctxArea = document.getElementById('visitorAreaChart');
    let visitorChart = null;

    const initialData = {
        labels: @json($labels),
        data: @json($data)
    };

    if (ctxArea && initialData.labels.length > 0) {
        visitorChart = new Chart(ctxArea, {
            type: 'line',
            data: {
                labels: initialData.labels,
                datasets: [{
                    label: 'Jumlah Pengunjung',
                    data: initialData.data,
                    fill: true,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    async function updateChart(interval) {
        try {
            intervalButton.textContent = 'Loading...';
            const response = await fetch(`{{ route('dashboard.chartData') }}?interval=${interval}`);
            const result = await response.json();

            if (visitorChart) {
                visitorChart.data.labels = result.labels;
                visitorChart.data.datasets[0].data = result.data;
                visitorChart.update();
            }

            intervalButton.textContent = `Last ${interval} Days`;
        } catch (error) {
            console.error('Gagal memuat data chart:', error);
            intervalButton.textContent = 'Error';
        }
    }

    dropdownItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const selectedInterval = item.dataset.interval;
            updateChart(selectedInterval);
            dropdownItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
        });
    });

    // === BAR CHART (Stock Levels) ===
    const ctxBar = document.getElementById('stockBarChart');
    const medicineNames = @json($medicineNames);
    const stockLevels = @json($stockLevels);

    if (ctxBar && medicineNames.length > 0) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: medicineNames,
                datasets: [{
                    label: 'Stock Akhir',
                    data: stockLevels,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // === PIE CHART (Medicine Category Distribution) ===
    const ctxPie = document.getElementById('categoryPieChart');
    const categoryData = @json($categoryData);

    if (ctxPie && Object.keys(categoryData).length > 0) {
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: Object.keys(categoryData),
                datasets: [{
                    data: Object.values(categoryData),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ]
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>
@endpush
