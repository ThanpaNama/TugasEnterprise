@extends('layout.app')

@section('content')

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f7fa;
    }
    h2 {
        font-weight: 700;
        color: #2c3e50;
    }
    .card {
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .btn-custom {
        background-color: #198754;
        color: white;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: 0.3s;
    }
    .btn-custom:hover {
        background-color: #157347;
    }
    table {
        font-size: 15px;
    }
</style>

<div class="container mt-5">

    <!-- Upload Excel -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="mb-3"><i class="bi bi-upload"></i> Upload Data Excel (format: XLSX)</h2>
            <div class="row">
                <form action="{{ route('coffee.import') }}" method="POST" enctype="multipart/form-data" class="row col-10 g-3">
                    @csrf
                    <div class="col-md-8">
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-cloud-arrow-up-fill"></i> Import Data
                        </button>
                    </div>
                </form>
                <form action="{{ route('coffee.delete') }}" method="POST" enctype="multipart/form-data" class="col-2 row g-3">
                    @csrf
                    <div>
                        <button type="Delete" class="btn btn-danger w-100">
                            <i class="bi bi-cloud-arrow-up-fill"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Laporan Rata-rata -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="mb-3"><i class="bi bi-graph-up"></i> Laporan Rata-rata Uang per Bulan</h2>
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-success">
                    <tr>
                        <th>Bulan</th>
                        <th>Rata-rata (Money)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthlyData as $row)
                        <tr>
                            <td>{{ $row->month }}</td>
                            <td>${{ number_format($row->avg_moneyy, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <!-- Forecast SES -->
    <div class="card mb-5">
        <div class="card-body">
            <h2 class="text-center mb-4"><i class="bi bi-bar-chart-line"></i> PERAMALAN PENJUALAN KOPI</h2>
            <form method="GET" action="{{ route('report') }}" class="mb-4 row justify-content-center align-items-end" id="alphaForm">
                <div class="col-md-3">
                    <label for="alpha" class="form-label fw-semibold">Nilai Alpha (α)</label>
                    <select name="alpha" id="alpha" class="form-select" onchange="document.getElementById('alphaForm').submit();">
                        @for ($i = 0.1; $i <= 0.9; $i += 0.1)
                            <option value="{{ number_format($i, 1) }}" {{ $alpha == number_format($i, 1) ? 'selected' : '' }}>
                                {{ number_format($i, 1) }}
                            </option>
                        @endfor
                    </select>
                </div>
            </form>
            <!-- Chart perbandingan Actual vs Forecast -->
            <canvas id="forecastChart" height="130"></canvas>
            <br>
            <table class="table table-bordered table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Bulan</th>
                        <th>Rata-rata (Actual)</th>
                        <th>Forecast</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forecast as $row)
                        <tr class="text-center">
                            <td>{{ \Carbon\Carbon::parse($row['month'].'-01')->translatedFormat('F Y') }}</td>
                            <td><span class="badge bg-success fs-6">${{ number_format($row['actual'], 2, ',', '.') }}</span></td>
                            <td><span class="badge bg-primary fs-6">${{ number_format($row['forecast'], 2, ',', '.') }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


        </div>
    </div>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart perbandingan Actual vs Forecast
    const ctx2 = document.getElementById('forecastChart').getContext('2d');
    const forecastChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: @json(array_map(fn($r) => \Carbon\Carbon::parse($r['month'].'-01')->translatedFormat('F Y'), $forecast)),
            datasets: [
                {
                    label: 'Actual',
                    data: @json(array_column($forecast, 'actual')),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.2)',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#198754'
                },
                {
                    label: 'Forecast',
                    data: @json(array_column($forecast, 'forecast')),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d6efd'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString("id-ID");
                        }
                    }
                }
            }
        }
    });
</script>

@endsection
