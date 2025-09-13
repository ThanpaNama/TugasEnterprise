<?php

use App\Http\Controllers\CoffeDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('coffe_sales.index');
});

Route::get('/coffee-sales', [CoffeDataController::class,'index'])->name('coffee_sales.index');
Route::post('/coffee-sales/upload', [CoffeDataController::class, 'upload'])->name('coffee_sales.upload');

