<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginPageController;
use App\Http\Controllers\ClubMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\CardsManageController;
use App\Http\Controllers\Master\CardTypesManageController;
use App\Http\Controllers\Master\GstRatesManageController;
use App\Http\Controllers\Master\MembershipDurationTypesManageController;
use App\Http\Controllers\Master\OperatorManageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [AuthenticatedSessionController::class, 'create'])->middleware('guest');

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/change-password', function () {
        return view('change-password');
    })->name('change.password');


    // master manage start
    Route::resource('manage-operators', OperatorManageController::class);
    Route::resource('manage-membership-duration-types', MembershipDurationTypesManageController::class);
    Route::resource('manage-card-types', CardTypesManageController::class);
    Route::resource('manage-cards', CardsManageController::class);
    Route::resource('manage-gst-rates', GstRatesManageController::class);
    // master manage end

    Route::prefix('club-member')->group(function () {
        Route::get('list', [ClubMemberController::class, 'list'])->name('club-member.list');
        Route::post('store', [ClubMemberController::class, 'store'])->name('club-member.store');
        Route::get('plan-price', [ClubMemberController::class, 'getPlanPrice'])->name('club-member-plan-price');
    });


});


require __DIR__.'/auth.php';
