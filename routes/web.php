<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CatalogItemController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\KnowledgeItemController;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Controllers\Api\AssistantChatController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BusinessPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BusinessPageController::class, 'index'])->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('catalog-items', CatalogItemController::class)->except('show');
        Route::resource('service-areas', ServiceAreaController::class)->except('show');
        Route::resource('knowledge-items', KnowledgeItemController::class)->except('show');

        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');

        Route::get('handovers', [HandoverController::class, 'index'])->name('handovers.index');
        Route::get('handovers/{handover}', [HandoverController::class, 'show'])->name('handovers.show');
        Route::post('handovers/{handover}/reply', [HandoverController::class, 'reply'])->name('handovers.reply');
        Route::post('handovers/{handover}/resolve', [HandoverController::class, 'resolve'])->name('handovers.resolve');
    });
});

Route::prefix('{businessSlug}')->middleware('business')->group(function () {
    Route::get('/', [BusinessPageController::class, 'show'])->name('business.show');
    Route::get('catalog', [BusinessPageController::class, 'catalog'])->name('business.catalog');
    Route::get('catalog/{itemSlug}', [BusinessPageController::class, 'catalogItem'])->name('business.catalog-item');
    Route::get('service', [BusinessPageController::class, 'service'])->name('business.service');
    Route::get('service/{areaSlug}', [BusinessPageController::class, 'serviceArea'])->name('business.service-area');
    Route::get('facilities', [BusinessPageController::class, 'facilities'])->name('business.facilities');
    Route::get('facilities/{facilityId}', [BusinessPageController::class, 'facility'])->whereNumber('facilityId')->name('business.facility');
    Route::get('info', [BusinessPageController::class, 'info'])->name('business.info');
    Route::get('staff', [BusinessPageController::class, 'staff'])->name('business.staff');
    Route::get('booking', [BusinessPageController::class, 'bookingScene'])->name('business.booking');

    Route::prefix('booking')->name('booking.')->middleware('throttle:20,1')->group(function () {
        Route::post('quote', [BookingController::class, 'quote'])->name('quote');
        Route::post('/', [BookingController::class, 'store'])->name('store');
    });

    Route::prefix('assistant')->name('assistant.')->group(function () {
        Route::post('start', [AssistantChatController::class, 'start'])->middleware('throttle:assistant-start')->name('start');
        Route::post('message', [AssistantChatController::class, 'message'])->middleware('throttle:assistant-message')->name('message');
        Route::get('history', [AssistantChatController::class, 'history'])->middleware('throttle:assistant-history')->name('history');
        Route::post('speech', [AssistantChatController::class, 'speech'])->middleware('throttle:assistant-speech')->name('speech');
    });
});
