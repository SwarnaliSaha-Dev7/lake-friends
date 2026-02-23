<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginPageController;
use App\Http\Controllers\Master\CardsManageController;
use App\Http\Controllers\Master\CardTypesManageController;
use App\Http\Controllers\Master\GstRatesManageController;
use App\Http\Controllers\Master\MembershipDurationTypesManageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [AuthenticatedSessionController::class, 'create'])->middleware('guest');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/change-password', function () {
        return view('change-password');
    })->name('change.password');

    Route::resource('manage-membership-duration-types', MembershipDurationTypesManageController::class);

    Route::resource('manage-card-types', CardTypesManageController::class);

    Route::resource('manage-cards', CardsManageController::class);

    Route::resource('manage-gst-rates', GstRatesManageController::class);
});


require __DIR__.'/auth.php';
