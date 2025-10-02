<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/calculate-commission', [TransactionController::class, 'calculate']);
