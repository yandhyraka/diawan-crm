<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HumanController;
use Illuminate\Support\Facades\Route;

Route::post('/human/update', [HumanController::class, 'humanUpdate']);
Route::get('/human/get/{human_uuid}', [HumanController::class, 'humanGet']);
Route::post('/human/relation/update', [HumanController::class, 'humanRelationUpdate']);
Route::post('/event/update', [EventController::class, 'eventUpdate']);