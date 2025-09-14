@extends('layout.app')

@section('content')

   <h2>Upload Coffee Sales Excel</h2>

   <form action="{{ route('coffee.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Import</button>
    </form>

    <div class="container">
    <h2>Laporan Rata-rata Uang per Bulan</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Rata-rata (Money)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monthlyData as $row)
                <tr>
                    <td>{{ $row->month }}</td>
                    <td>{{ number_format($row->avg_moneyy, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="container">
    <h2 class="text-center mb-4">📈 Forecast dengan Single Exponential Smoothing</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Bulan</th>
                <th>Rata-rata (Actual)</th>
                <th>Forecast</th>
            </tr>
        </thead>
        <tbody>
            @foreach($forecast as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['month'].'-01')->translatedFormat('F Y') }}</td>
                    <td>Rp {{ number_format($row['actual'], 2, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['forecast'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
