<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanController;

Route::post('/human/update', [HumanController::class, 'humanUpdate']);
Route::post('/human/relation/update', [HumanController::class, 'humanRelationUpdate']);
