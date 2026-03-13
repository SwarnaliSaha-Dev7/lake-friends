<?php

use App\Http\Controllers\ActionApprovalController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginPageController;
use App\Http\Controllers\ClubMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodItemManageController;
use App\Http\Controllers\LiquorItemManageController;
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
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [AuthenticatedSessionController::class, 'create'])->middleware('guest');

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::post('send-otp', [UserController::class, 'sendOTP'])->name('sendOTP');
Route::post('verify-otp', [UserController::class, 'verifyOTP'])->name('verifyOTP');//verify OTP
Route::post('reset-new-password', [UserController::class, 'resetNewPassword'])->name('resetNewPassword');
// Route::post('reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/change-password', [UserController::class, 'changePassword'])->name('change.password');


    // master manage start
    Route::middleware(['role:admin'])->group(function () {
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
    });
    // master manage end

    Route::prefix('club-member')->group(function () {
        Route::get('list', [ClubMemberController::class, 'list'])->name('club-member.list');
        Route::post('store', [ClubMemberController::class, 'store'])->name('club-member.store');
        Route::get('plan-price', [ClubMemberController::class, 'getPlanPrice'])->name('club-member-plan-price');
        Route::get('/view/{id}', [ClubMemberController::class, 'view'])->name('club-member.view');
        Route::post('/update', [ClubMemberController::class, 'update'])->name('club-member.update');
        Route::get('/membership-plan/{id}', [ClubMemberController::class, 'membershipPlan'])->name('club-member.membership-plan');
        Route::get('/fetch-wallet-balance/{id}', [ClubMemberController::class, 'fetchWalletBalance'])->name('club-member.fetch-wallet-balance');
        Route::post('/recharge-wallet-balance', [ClubMemberController::class, 'rechargeWalletBalance'])->name('club-member.recharge-wallet-balance');
        Route::delete('/club-member/{id}', [ClubMemberController::class, 'delete'])->name('club-member.delete');
    });

    Route::prefix('swimming-member')->group(function () {
        Route::get('/list', [SwimmingMemberController::class, 'list'])->name('swimming-member.list');
        Route::post('store', [SwimmingMemberController::class, 'store'])->name('swimming-member.store');
        Route::get('plan-price', [SwimmingMemberController::class, 'getPlanPrice'])->name('swimming-member.plan-price');
        Route::get('/view/{id}', [SwimmingMemberController::class, 'view'])->name('swimming-member.view');
        Route::post('/update', [SwimmingMemberController::class, 'update'])->name('swimming-member.update');
        Route::get('/membership-plan/{id}', [SwimmingMemberController::class, 'membershipPlan'])->name('swimming-member.membership-plan');
        Route::get('/fetch-wallet-balance/{id}', [SwimmingMemberController::class, 'fetchWalletBalance'])->name('swimming-member.fetch-wallet-balance');
        Route::post('/recharge-wallet-balance', [SwimmingMemberController::class, 'rechargeWalletBalance'])->name('swimming-member.recharge-wallet-balance');
        Route::get('/delete/{id}', [SwimmingMemberController::class, 'delete'])->name('swimming-member.delete');
    });

    Route::prefix('manage-member-approval-status')->controller(ActionApprovalController::class)->group(function () {
        Route::get('list', 'index')->name('memberActionApproval.list');
        Route::get('reject/{id}', 'reject')->name('memberActionApproval.reject');
        Route::get('approve/{id}', 'approve')->name('memberActionApproval.approve');
        Route::get('view/{id}', 'view')->name('memberActionApproval.view');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/all-action-approval-list', [ActionApprovalController::class, 'allApprovalList'])->name('all-action-approval-list');
    });

    Route::get('/notifications/read-all', [DashboardController::class, 'readAllNotification'])->name('readAllNotification');

    Route::get('get-member-details/{cardNo}', [DashboardController::class, 'fetchMemberDetailsByCard'])->name('getMemberDetails');

    Route::resource('manage-food-items', FoodItemManageController::class);
    Route::prefix('manage-food-item-price-approval')->controller(ActionApprovalController::class)->group(function () {
        Route::get('list', 'foodItemPriceLIst')->name('foodItemPriceApproval.list');
        Route::get('reject/{id}', 'reject')->name('foodItemPriceApproval.reject');
        Route::get('approve/{id}', 'approve')->name('foodItemPriceApproval.approve');
        Route::get('view/{id}', 'view')->name('foodItemPriceApproval.view');
    });
    Route::post('price-request', [FoodItemManageController::class, 'requestPriceChange'])->name('foodItemPriceApproval.request');

    Route::resource('manage-liquor-items', LiquorItemManageController::class);
});


require __DIR__ . '/auth.php';
