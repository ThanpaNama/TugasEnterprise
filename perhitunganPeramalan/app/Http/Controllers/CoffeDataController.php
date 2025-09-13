<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\coffeData;
use Maatwebsite\Excel\Facades\Excel;
class CoffeDataController extends Controller
{
    public function index(){
        return view('coffe_sales.index');
    }
    public function upload(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        $path = $request->file('file')->getRealPath();

        Excel::load($path, function($reader){
            foreach($reader->toArray()as $row){
                if(!empty($row)){
                    coffeData::create([
                        'date' => isset($row['date']) ? $row['date'] : null,
                        'datetime' => isset($row['datetime']) ? $row['datetime'] : null,
                        'cash_type' => isset($row['cash_type']) ? $row['cash_type'] : null,
                        'card' => isset($row['card']) ? $row['card'] : null,
                        'moneyy' => isset($row['moneyy']) ? $row['moneyy'] : 0,
                        'coffe_name' => isset($row['coffe_name']) ? $row['coffe_name'] : null,
                    ]);
                }
            }
        });
        return redirect()->back()->with('success', 'Data Imported Successfully');
    }
}
