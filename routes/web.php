<?php

use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StationController::class, 'index'])->name('stations.index');
Route::get('/distributore/{station}', [StationController::class, 'show'])->name('stations.show');
