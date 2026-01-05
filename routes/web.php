<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CourseCategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\LocationImageController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffImageController;
use App\Http\Controllers\Member\MypageController;
use App\Http\Controllers\Member\ReservationController as MemberReservationController;
use App\Http\Controllers\Member\SessionController as MemberSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');

    Route::get('/sessions', [MemberSessionController::class, 'index'])->name('member.sessions.index');
    Route::get('/sessions/{session}/reserve', [MemberSessionController::class, 'reserve'])->name('member.sessions.reserve');

    Route::post('/reservations/normal', [MemberReservationController::class, 'storeNormal'])->name('member.reservations.normal.store');
    Route::post('/reservations/trial', [MemberReservationController::class, 'storeTrial'])->name('member.reservations.trial.store');
    Route::post('/reservations/{reservation}/cancel', [MemberReservationController::class, 'cancel'])->name('member.reservations.cancel');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');

        Route::resource('programs', ProgramController::class)->except(['show']);
        Route::resource('courses', CourseController::class)->except(['show']);
        Route::get('courses/{course}/categories', [CourseCategoryController::class, 'index'])->name('courses.categories.index');
        Route::post('courses/{course}/categories', [CourseCategoryController::class, 'store'])->name('courses.categories.store');
        Route::delete('courses/{course}/categories', [CourseCategoryController::class, 'destroy'])->name('courses.categories.destroy');
        Route::resource('plans', PlanController::class)->except(['show']);

        Route::resource('staffs', StaffController::class)->except(['show']);
        Route::get('staffs/{staff}/images', [StaffImageController::class, 'index'])->name('staffs.images.index');
        Route::post('staffs/{staff}/images', [StaffImageController::class, 'store'])->name('staffs.images.store');
        Route::delete('staffs/{staff}/images/{image}', [StaffImageController::class, 'destroy'])->name('staffs.images.destroy');

        Route::resource('locations', LocationController::class)->except(['show']);
        Route::get('locations/{location}/images', [LocationImageController::class, 'index'])->name('locations.images.index');
        Route::post('locations/{location}/images', [LocationImageController::class, 'store'])->name('locations.images.store');
        Route::delete('locations/{location}/images/{image}', [LocationImageController::class, 'destroy'])->name('locations.images.destroy');

        Route::resource('sessions', SessionController::class)->except(['show']);
    });
});
