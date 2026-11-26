<?php

use App\Http\Controllers\Admin\AdminKosController;
use App\Http\Controllers\Admin\AdminMasterController;
use App\Http\Controllers\Admin\AdminMitraController;
use App\Http\Controllers\Admin\GovernanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceVerificationController;
use App\Http\Controllers\KosController;
use App\Http\Controllers\Mitra\BoardingHouseController as MitraBoardingHouseController;
use App\Http\Controllers\Mitra\BookingVerificationController;
use App\Http\Controllers\Mitra\MitraProfileController;
use App\Http\Controllers\Mitra\OperationsController;
use App\Http\Controllers\Mitra\RoomTypeController;
use App\Http\Controllers\Mitra\SurveyManagementController;
use App\Http\Controllers\MitraRegistrationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Penyewa\BookingController as PenyewaBookingController;
use App\Http\Controllers\Penyewa\MaintenanceController;
use App\Http\Controllers\Penyewa\SavedKosController;
use App\Http\Controllers\Penyewa\SurveyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicFeatureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/kos', [KosController::class, 'index'])->name('kos.index');
Route::get('/kos/bandingkan', [PublicFeatureController::class, 'compare'])->name('kos.compare');
Route::get('/kos/{boardingHouse:slug}', [KosController::class, 'show'])->name('kos.show');
Route::post('/kos/{boardingHouse}/lapor', [PublicFeatureController::class, 'storeReport'])->name('kos.report');
Route::get('/gabung-mitra', [MitraRegistrationController::class, 'info'])->name('mitra.info');
Route::get('/verifikasi-invoice/{bookingCode}/{token}', [InvoiceVerificationController::class, 'show'])->name('invoice.verify');

Route::middleware('guest')->group(function () {
    Route::get('/register/mitra', [MitraRegistrationController::class, 'create'])->name('register.mitra');
    Route::post('/register/mitra', [MitraRegistrationController::class, 'store'])->name('register.mitra.store');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifikasi/{notification}/baca', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::post('/kos/{boardingHouse}/tanya', [PublicFeatureController::class, 'storeQuestion'])->name('kos.questions.store');
});

Route::middleware(['auth', 'role:penyewa'])->prefix('penyewa')->name('penyewa.')->group(function () {
    Route::get('/pesanan', [PenyewaBookingController::class, 'index'])->name('bookings.index');
    Route::post('/pesanan', [PenyewaBookingController::class, 'store'])->name('bookings.store');
    Route::get('/pesanan/{booking}', [PenyewaBookingController::class, 'show'])->name('bookings.show');
    Route::post('/pesanan/{booking}/payment', [PenyewaBookingController::class, 'uploadPayment'])->name('bookings.payment');
    Route::patch('/pesanan/{booking}/cancel', [PenyewaBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/pesanan/{booking}/renew', [PenyewaBookingController::class, 'renew'])->name('bookings.renew');
    Route::get('/pesanan/{booking}/invoice', [PenyewaBookingController::class, 'invoicePdf'])->name('bookings.invoice');
    Route::get('/pesanan/{booking}/kontrak', [PenyewaBookingController::class, 'contractPdf'])->name('bookings.contract');
    Route::post('/pesanan/{booking}/review', [PenyewaBookingController::class, 'storeReview'])->name('bookings.review');

    Route::get('/favorit', [SavedKosController::class, 'index'])->name('saved.index');
    Route::post('/favorit/{boardingHouse}', [SavedKosController::class, 'toggle'])->name('saved.toggle');
    Route::post('/waitlist/{roomType}', [PublicFeatureController::class, 'toggleWaitlist'])->name('waitlist.toggle');

    Route::get('/survei', [SurveyController::class, 'index'])->name('surveys.index');
    Route::post('/survei/{boardingHouse}', [SurveyController::class, 'store'])->name('surveys.store');
    Route::patch('/survei/{surveyBooking}/cancel', [SurveyController::class, 'cancel'])->name('surveys.cancel');

    Route::get('/keluhan', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/keluhan', [MaintenanceController::class, 'store'])->name('maintenance.store');
});

Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/profil', [MitraProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [MitraProfileController::class, 'update'])->name('profile.update');

    Route::get('/kos', [MitraBoardingHouseController::class, 'index'])->name('kos.index');
    Route::post('/maps/resolve', [MitraBoardingHouseController::class, 'resolveMapLocation'])->name('maps.resolve');
    Route::get('/kos/tambah', [MitraBoardingHouseController::class, 'create'])->name('kos.create');
    Route::post('/kos', [MitraBoardingHouseController::class, 'store'])->name('kos.store');
    Route::get('/kos/{boardingHouse}/edit', [MitraBoardingHouseController::class, 'edit'])->name('kos.edit');
    Route::put('/kos/{boardingHouse}', [MitraBoardingHouseController::class, 'update'])->name('kos.update');
    Route::delete('/kos/{boardingHouse}', [MitraBoardingHouseController::class, 'destroy'])->name('kos.destroy');
    Route::delete('/kos/{boardingHouse}/foto/{image}', [MitraBoardingHouseController::class, 'destroyImage'])->name('kos.images.destroy');
    Route::get('/kos/{boardingHouse}/poster-qr', [OperationsController::class, 'posterQrPdf'])->name('kos.poster-qr');

    Route::get('/kos/{boardingHouse}/kamar', [RoomTypeController::class, 'index'])->name('rooms.index');
    Route::post('/kos/{boardingHouse}/kamar', [RoomTypeController::class, 'store'])->name('rooms.store');
    Route::get('/kos/{boardingHouse}/kamar/{roomType}/edit', [RoomTypeController::class, 'edit'])->name('rooms.edit');
    Route::put('/kos/{boardingHouse}/kamar/{roomType}', [RoomTypeController::class, 'update'])->name('rooms.update');
    Route::patch('/kos/{boardingHouse}/kamar/{roomType}/stock', [RoomTypeController::class, 'adjustStock'])->name('rooms.stock');
    Route::delete('/kos/{boardingHouse}/kamar/{roomType}', [RoomTypeController::class, 'destroy'])->name('rooms.destroy');

    Route::post('/kos/{boardingHouse}/kamar/{roomType}/unit', [OperationsController::class, 'storeUnit'])->name('units.store');
    Route::patch('/kos/{boardingHouse}/kamar/{roomType}/unit/{unit}', [OperationsController::class, 'updateUnit'])->name('units.update');
    Route::delete('/kos/{boardingHouse}/kamar/{roomType}/unit/{unit}', [OperationsController::class, 'destroyUnit'])->name('units.destroy');

    Route::get('/pesanan', [BookingVerificationController::class, 'index'])->name('bookings.index');
    Route::patch('/pesanan/{booking}/approve', [BookingVerificationController::class, 'approve'])->name('bookings.approve');
    Route::patch('/pesanan/{booking}/reject', [BookingVerificationController::class, 'reject'])->name('bookings.reject');
    Route::patch('/pesanan/{booking}/complete', [BookingVerificationController::class, 'complete'])->name('bookings.complete');
    Route::get('/laporan/pdf', [BookingVerificationController::class, 'reportPdf'])->name('report.pdf');
    Route::get('/laporan/csv', [OperationsController::class, 'exportCsv'])->name('report.csv');

    Route::get('/survei', [SurveyManagementController::class, 'index'])->name('surveys.index');
    Route::patch('/survei/{surveyBooking}', [SurveyManagementController::class, 'update'])->name('surveys.update');

    Route::get('/keluhan', [OperationsController::class, 'ticketsIndex'])->name('tickets.index');
    Route::patch('/keluhan/{ticket}', [OperationsController::class, 'updateTicket'])->name('tickets.update');

    Route::get('/promo', [OperationsController::class, 'promosIndex'])->name('promos.index');
    Route::post('/promo', [OperationsController::class, 'storePromo'])->name('promos.store');
    Route::patch('/promo/{promo}/toggle', [OperationsController::class, 'togglePromo'])->name('promos.toggle');
    Route::delete('/promo/{promo}', [OperationsController::class, 'destroyPromo'])->name('promos.destroy');

    Route::get('/pengumuman', [OperationsController::class, 'announcementsIndex'])->name('announcements.index');
    Route::post('/pengumuman', [OperationsController::class, 'storeAnnouncement'])->name('announcements.store');
    Route::delete('/pengumuman/{announcement}', [OperationsController::class, 'destroyAnnouncement'])->name('announcements.destroy');
    Route::patch('/pertanyaan/{question}/jawab', [OperationsController::class, 'answerQuestion'])->name('questions.answer');

    Route::get('/keuangan', [OperationsController::class, 'expensesIndex'])->name('expenses.index');
    Route::post('/keuangan', [OperationsController::class, 'storeExpense'])->name('expenses.store');
    Route::delete('/keuangan/{expense}', [OperationsController::class, 'destroyExpense'])->name('expenses.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/mitra', [AdminMitraController::class, 'index'])->name('mitra.index');
    Route::patch('/mitra/{mitraProfile}/approve', [AdminMitraController::class, 'approve'])->name('mitra.approve');
    Route::patch('/mitra/{mitraProfile}/reject', [AdminMitraController::class, 'reject'])->name('mitra.reject');

    Route::get('/kos', [AdminKosController::class, 'index'])->name('kos.index');
    Route::patch('/kos/{boardingHouse}/approve', [AdminKosController::class, 'approve'])->name('kos.approve');
    Route::patch('/kos/{boardingHouse}/unpublish', [AdminKosController::class, 'unpublish'])->name('kos.unpublish');
    Route::patch('/kos/{boardingHouse}/reject', [AdminKosController::class, 'reject'])->name('kos.reject');
    Route::patch('/kos/{boardingHouse}/verify-field', [GovernanceController::class, 'toggleFieldVerified'])->name('kos.verify-field');

    Route::get('/master', [AdminMasterController::class, 'masterIndex'])->name('master.index');
    Route::post('/areas', [AdminMasterController::class, 'storeArea'])->name('areas.store');
    Route::put('/areas/{area}', [AdminMasterController::class, 'updateArea'])->name('areas.update');
    Route::delete('/areas/{area}', [AdminMasterController::class, 'destroyArea'])->name('areas.destroy');
    Route::post('/facilities', [AdminMasterController::class, 'storeFacility'])->name('facilities.store');
    Route::delete('/facilities/{facility}', [AdminMasterController::class, 'destroyFacility'])->name('facilities.destroy');

    Route::get('/monitoring', [AdminMasterController::class, 'monitoring'])->name('monitoring.index');
    Route::patch('/users/{user}/toggle', [AdminMasterController::class, 'toggleUser'])->name('users.toggle');
    Route::patch('/reviews/{review}/toggle', [AdminMasterController::class, 'toggleReview'])->name('reviews.toggle');
    Route::get('/laporan/pdf', [AdminMasterController::class, 'reportPdf'])->name('report.pdf');
    Route::get('/laporan/csv', [GovernanceController::class, 'exportCsv'])->name('report.csv');

    Route::get('/laporan-listing', [GovernanceController::class, 'reportsIndex'])->name('reports.index');
    Route::patch('/laporan-listing/{report}', [GovernanceController::class, 'handleReport'])->name('reports.handle');
    Route::get('/audit-log', [GovernanceController::class, 'auditIndex'])->name('audit.index');
});

require __DIR__.'/auth.php';
