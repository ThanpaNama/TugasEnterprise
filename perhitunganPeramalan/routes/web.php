<?php

use App\Http\Controllers\CoffeDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoffeDataController::class, 'report'])->name('report');


Route::post('/coffee-sales', [CoffeDataController::class,'import'])->name('coffee.import');
Route::post('/coffee-delete', [CoffeDataController::class,'delete'])->name('coffee.delete');
Route::get('/coffee-report', [CoffeDataController::class, 'report'])->name('coffe.report');


