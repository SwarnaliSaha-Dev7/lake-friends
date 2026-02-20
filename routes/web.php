<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginPageController;
use App\Http\Controllers\ClubMemberController;
use App\Http\Controllers\Master\OperatorManageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [AuthenticatedSessionController::class, 'create'])->middleware('guest');

Route::get('/dashboard', function () {
    $page_title = 'Dashboard';
    $title = 'Dashboard';
    return view('dashboard', compact('title','page_title'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/change-password', function () {
        return view('change-password');
    })->name('change.password');


    Route::resource('manage-operators', OperatorManageController::class);

    // Route::get('/club-member', function () {
    //     $title = 'Club Member list';
    //     $page_title = 'Manage Club Member';
    //     return view('club_member.list', compact('title','page_title'));
    // })->middleware(['auth', 'verified'])->name('dashboard');

    Route::prefix('club-member')->group(function () {
        Route::get('/list', [ClubMemberController::class, 'list'])->name('club-member.list');
    });

});

require __DIR__.'/auth.php';
