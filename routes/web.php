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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/mypage', function () {
        return view('member.mypage');
    })->name('mypage');
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
