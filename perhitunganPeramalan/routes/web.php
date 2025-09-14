<?php

use App\Http\Controllers\CoffeDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoffeDataController::class, 'report']);


Route::post('/coffee-sales', [CoffeDataController::class,'import'])->name('coffee.import');
Route::get('/coffee-report', [CoffeDataController::class, 'report'])->name('coffe.report');


