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

// The {employee} ID is validated by App\Http\Requests\EmployeeIdRequest.
Route::apiResource('employees', EmployeeController::class)
    ->only(['index', 'show', 'store', 'destroy']);

// Future: add 'update' to the only() list above to expose
// PUT/PATCH /api/employees/{employee} once EmployeeController::update() exists.
