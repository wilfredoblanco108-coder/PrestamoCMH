<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrestamoController;

Route::get('/', function () {
    return view('welcome');
})->name('inicio');

Route::get('/prestamos', [
    PrestamoController::class,
    'index'
])->name('prestamos.index');

Route::post('/prestamos/calcular', [
    PrestamoController::class,
    'calcular'
])->name('prestamos.calcular');