<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',[\App\Http\Controllers\StartController::class,'welcome'])->name('welcome');

Route::get('/cities/{country_name}',[\App\Http\Controllers\CitiesController::class,'index'])->name('city.index');
