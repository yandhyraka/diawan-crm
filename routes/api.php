<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanController;

Route::post('/human/update', [HumanController::class, 'humanUpdate']);
