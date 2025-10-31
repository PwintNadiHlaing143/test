<?php

use App\Http\Controllers\Township\TownshipController;

use Illuminate\Support\Facades\Route;


// for townships
Route::get('/townships', [TownshipController::class, 'index']);
Route::get('/townships/{id}', [TownshipController::class, 'show']);
Route::get('/townships/search/{name}', [TownshipController::class, 'search']);