<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    // Registration disabled - users created by admin only
    // Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Verify letter number (public - untuk cek keaslian surat)
Route::get('/verify-letter/{nomorSurat}', [TicketController::class, 'verifyLetter'])->where('nomorSurat', '.*');
Route::get('/verify-letter', [TicketController::class, 'verifyLetterQuery']); // Alternatif dengan query string

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    // Ticket routes
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/statistics', [TicketController::class, 'statistics']);
        Route::get('/lecturers', [TicketController::class, 'getLecturers']);
        Route::get('/{id}', [TicketController::class, 'show']);
        
        // Mahasiswa routes
        Route::middleware('role:mahasiswa')->group(function () {
            Route::post('/', [TicketController::class, 'store']);
            Route::put('/{id}', [TicketController::class, 'update']);
        });

        // Dosen routes
        Route::middleware('role:dosen')->group(function () {
            Route::post('/{id}/review', [TicketController::class, 'review']);
            Route::post('/{id}/approve', [TicketController::class, 'approve']);
            Route::post('/{id}/reject', [TicketController::class, 'reject']);
        });

        // Admin routes
        Route::middleware('role:admin')->group(function () {
            Route::post('/{id}/send-to-lecturer', [TicketController::class, 'sendToLecturer']);
            Route::post('/{id}/reject', [TicketController::class, 'adminReject']);
            Route::post('/{id}/complete', [TicketController::class, 'complete']);
        });
    });

    // Document routes
    Route::prefix('tickets/{ticketId}/documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/', [DocumentController::class, 'upload']);
    });

    Route::prefix('documents')->group(function () {
        Route::get('/{id}/download', [DocumentController::class, 'download']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
    });

    // User management routes (Admin only)
    Route::middleware('role:admin')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
});
