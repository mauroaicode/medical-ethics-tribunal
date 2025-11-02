<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Admin routes
Route::prefix('admin')->group(function () {
    require __DIR__.'/api/admin/auth.php';
    require __DIR__.'/api/admin/users.php';
    require __DIR__.'/api/admin/complainants.php';
    require __DIR__.'/api/admin/doctors.php';
    require __DIR__.'/api/admin/magistrates.php';
    require __DIR__.'/api/admin/templates.php';
    require __DIR__.'/api/admin/processes.php';
    require __DIR__.'/api/admin/proceedings.php';
});
