<?php

use App\Http\Controllers\LabController;
use App\Http\Controllers\LossCalculatorController;
use App\Http\Controllers\TopologyController;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('home');
});


Route::resource('/home', \App\Http\Controllers\HomeController::class)->name('index', 'home');
Route::resource('/lab', \App\Http\Controllers\LabController::class)->name('index', 'lab');
Route::get('/lab/{lab}/topologi', [LabController::class, 'topologi'])->name('lab.canvas');
Route::post('/topologi/save/{id}', [TopologyController::class, 'save']);
Route::get('/topologi/load/{id}', [TopologyController::class, 'load']);
