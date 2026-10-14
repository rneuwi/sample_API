<?php

use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes in this file are prefixed with /api and use the "api" middleware
| group (see RouteServiceProvider).
|
*/

// IDs must be positive integers without leading zeros, capped at 18 digits so
// they always fit in a PHP int. Anything else returns 404 before reaching the
// controller.
Route::apiResource('employees', EmployeeController::class)
    ->only(['index', 'show', 'store', 'destroy'])
    ->where(['employee' => '[1-9][0-9]{0,17}']);

// Future: add 'update' to the only() list above to expose
// PUT/PATCH /api/employees/{employee} once EmployeeController::update() exists.
