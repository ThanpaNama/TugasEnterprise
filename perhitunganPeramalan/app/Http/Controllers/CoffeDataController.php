<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\coffeData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
class CoffeDataController extends Controller
{
    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls'
    ]);

    $file = $request->file('file')->getPathname();

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // skip header

        // --- Format tanggal ---
        $date = null;
        if (!empty($row[0])) {
            $dateString = $row[0];

            // Jika Excel date serial number
            if (is_numeric($dateString)) {
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString)
                        ->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            } else {
                // Bersihkan karakter Jepang
                $dateString = preg_replace('/[年月日]/u', '-', $dateString);
                $dateString = str_replace('--', '-', $dateString);
                try {
                    $date = Carbon::parse($dateString)->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            }
        }

        // --- Format datetime ---
        $datetime = null;
        if (!empty($row[1])) {
            $datetimeString = $row[1];

            if (is_numeric($datetimeString)) {
                try {
                    $datetime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($datetimeString)
                        ->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $datetime = null;
                }
            } else {
                $datetimeString = preg_replace('/[年月日時分秒]/u', ' ', $datetimeString);
                try {
                    $datetime = Carbon::parse($datetimeString)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $datetime = null;
                }
            }
        }

        // --- Format money ---
        $money = null;
        if (!empty($row[4])) {
            // Ambil hanya angka, minus, dan titik desimal
            $money = preg_replace('/[^0-9.\-]/', '', $row[4]);
            $money = $money !== '' ? (float) $money : null;
        }

        CoffeData::create([
            'date'        => $date,
            'datetime'    => $datetime,
            'cash_type'   => $row[2] ?? null,
            'card'        => $row[3] ?? null,
            'moneyy'       => $money,
            'coffe_name' => $row[5] ?? null,
        ]);
    }

    return back()->with('success', 'Data berhasil diimport');
}

    // Export Database ke Excel
    public function report()
    {
        $data = DB::table('coffe_data')->get();
        $monthlyData = DB::table('coffe_data')
            ->select(
                DB::raw("DATE_FORMAT(datetime, '%Y-%m') as month"),
                DB::raw("AVG(moneyy) as avg_moneyy")
            )
            ->groupBy(DB::raw("DATE_FORMAT(datetime, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(datetime, '%Y-%m')"))
            ->get();

        if ($monthlyData->isEmpty()) {
            return view('coffe_sales.index', ['monthlyData' => $monthlyData, 'forecast' => [], 'nextForecast' => null, 'alpha' => 0.5, 'mae' => null, 'mse' => null, 'mape' => null]);
        }

        $alpha = request('alpha', 0.5);
        $forecast = [];
        $totalError = 0;
        $totalSquaredError = 0;
        $totalPercentageError = 0;
        $n = $monthlyData->count();

        // Inisialisasi: F_1 = Y_1 (bisa juga di-set null jika mau)
        $first = $monthlyData->first();
        $previousForecast = (float) $first->avg_moneyy;
        $previousActual = (float) $first->avg_moneyy;

        // catat forecast untuk periode pertama (seringkali diset sama dengan actual)
        $forecast[] = [
            'month'    => $first->month,
            'actual'   => round($previousActual, 2),
            'forecast' => round($previousForecast, 2),
            'error'    => 0, // Error untuk periode pertama dianggap 0
            'abs_error' => 0 // Absolute error untuk periode pertama dianggap 0
        ];

        // mulai dari index 1 (periode ke-2)
        foreach ($monthlyData->slice(1) as $row) {
            $actual = (float) $row->avg_moneyy;

            // Forecast for current month t = alpha * Y_{t-1} + (1-alpha) * F_{t-1}
            $forecastValue = $alpha * $previousActual + (1 - $alpha) * $previousForecast;

            // Hitung error dan absolute error
            $error = $actual - $forecastValue;
            $absError = abs($error);
            $absError = abs($error);
            $percentageError = $actual != 0 ? abs($error / $actual) * 100 : 0;

            $forecast[] = [
                'month'    => $row->month,
                'actual'   => round($actual, 2),
                'forecast' => round($forecastValue, 2),
                'error'    => round($error, 2),
                'abs_error' => round($absError, 2)
            ];

            // update total error metrics
            $totalError += $absError;
            $totalSquaredError += pow($error, 2);
            $totalPercentageError += $percentageError;

            // update previous values for next iteration
            $previousForecast = $forecastValue;
            $previousActual = $actual;
        }

        $effectiveN = $n > 1 ? $n - 1 : 1;  
        $mae = $totalError / $effectiveN;
        $mse = $totalSquaredError / $effectiveN;
        $mape = $totalPercentageError / $effectiveN;

        // Jika ingin forecast untuk bulan berikutnya (t+1):
        // gunakan actual terakhir dan forecast terakhir:
        $lastActual = (float) $monthlyData->last()->avg_moneyy;
        $nextForecast = $alpha * $lastActual + (1 - $alpha) * $previousForecast;

        return view('coffe_sales.index', compact('monthlyData', 'forecast', 'nextForecast', 'alpha', 'data', 'mae', 'mse', 'mape'));
    }

    public function delete(){
        DB::table('coffe_data')->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }
}
