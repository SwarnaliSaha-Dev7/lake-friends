<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginPageController;
use App\Http\Controllers\ClubMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodItemManageController;
use App\Http\Controllers\Master\CardsManageController;
use App\Http\Controllers\Master\CardTypesManageController;
use App\Http\Controllers\Master\FineRulesManageController;
use App\Http\Controllers\Master\FoodCategoryManageController;
use App\Http\Controllers\Master\GstRatesManageController;
use App\Http\Controllers\Master\LiquorCategoryManageController;
use App\Http\Controllers\Master\LockerManageController;
use App\Http\Controllers\Master\MembershipDurationTypesManageController;
use App\Http\Controllers\Master\MinimumSpendRuleManageController;
use App\Http\Controllers\Master\OperatorManageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SwimmingMemberController;
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
    Route::resource('manage-fine-rules', FineRulesManageController::class);
    Route::resource('manage-minimum-spend-rules', MinimumSpendRuleManageController::class);
    Route::resource('manage-food-categories', FoodCategoryManageController::class);
    Route::resource('manage-liquor-categories', LiquorCategoryManageController::class);
    Route::resource('manage-lockers', LockerManageController::class);
    // master manage end

    Route::prefix('club-member')->group(function () {
        Route::get('list', [ClubMemberController::class, 'list'])->name('club-member.list');
        Route::post('store', [ClubMemberController::class, 'store'])->name('club-member.store');
        Route::get('plan-price', [ClubMemberController::class, 'getPlanPrice'])->name('club-member-plan-price');
    });

    Route::prefix('swimming-member')->group(function () {
        Route::get('/list', [SwimmingMemberController::class, 'list'])->name('swimming-member.list');
        Route::post('store', [SwimmingMemberController::class, 'store'])->name('swimming-member.store');
        Route::get('plan-price', [SwimmingMemberController::class, 'getPlanPrice'])->name('swimming-member.plan-price');
        Route::get('/view/{id}', [SwimmingMemberController::class, 'view'])->name('swimming-member.view');
        Route::post('/update', [SwimmingMemberController::class, 'update'])->name('swimming-member.update');
        Route::get('/membership-plan/{id}', [SwimmingMemberController::class, 'membershipPlan'])->name('swimming-member.membership-plan');
        Route::get('/fetch-wallet-balance/{id}', [SwimmingMemberController::class, 'fetchWalletBalance'])->name('swimming-member.fetch-wallet-balance');
        Route::get('/delete/{id}', [SwimmingMemberController::class, 'delete'])->name('swimming-member.delete');
    });

    Route::resource('manage-food-items', FoodItemManageController::class);

});


require __DIR__ . '/auth.php';
