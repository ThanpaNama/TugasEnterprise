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
    // Ambil data rata-rata per bulan
    $monthlyData = DB::table('coffe_data')
        ->select(
            DB::raw("DATE_FORMAT(datetime, '%Y-%m') as month"),
            DB::raw("AVG(moneyy) as avg_moneyy")
        )
        ->groupBy(DB::raw("DATE_FORMAT(datetime, '%Y-%m')"))
        ->orderBy(DB::raw("DATE_FORMAT(datetime, '%Y-%m')"))
        ->get();

    // Hitung Single Exponential Smoothing (SES)
    $alpha = 0.5; // smoothing constant (bisa Anda sesuaikan)
    $forecast = [];
    $previousForecast = $monthlyData[0]->avg_moneyy ?? 0;

    foreach ($monthlyData as $index => $row) {
        if ($index == 0) {
            $forecastValue = $previousForecast; // forecast pertama = nilai aktual pertama
        } else {
            $forecastValue = $alpha * $row->avg_moneyy + (1 - $alpha) * $previousForecast;
            $previousForecast = $forecastValue;
        }

        $forecast[] = [
            'month'    => $row->month,
            'actual'   => round($row->avg_moneyy, 2),
            'forecast' => round($forecastValue, 2),
        ];
    }

    return view('coffe_sales.index', compact('monthlyData', 'forecast'));
}


    public function perhitunganSES(){
        $monthlyData = DB::table('coffe_data')
        ->select(
            DB::raw("DATE_FORMAT(datetime, '%Y-%m') as month"),
            DB::raw("AVG(moneyy) as avg_moneyy")
        )
        ->groupBy(DB::raw("DATE_FORMAT(datetime, '%Y-%m')"))
        ->orederBy(DB::raw("DATE_FORMAT(datetime, '%Y-%m"))
        ->get();
        $alpha = 0.5;
        $forecast = [];
        $previousForecast = $monthlyData[0]->avg_moneyy;

        foreach($monthlyData as $index => $row){
            if($index == 0){
                $forecastvalue = $previousForecast;
            }else{
                $forecastvalue = $alpha * $row->avg_moneyy + (1 - $alpha) * $previousForecast;
                $previousForecast = $forecastvalue;
            }
            $forecast[] = [
                'month' => $row->month,
                'actual' => round($row->avg_moneyy, 2),
                'forecast' => round($forecastvalue,2)
            ];
        }
        return view('coffe_sales.index', compact('forecast'));
    }

    public function delete(){
        DB::table('coffe_data')->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }
}
