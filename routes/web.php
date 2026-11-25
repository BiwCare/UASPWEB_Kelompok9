<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [HomeController::class, 'catalog'])->name('catalog');
Route::get('/alat/{peralatan:slug}', [HomeController::class, 'show'])->name('equipment.show');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/daftar', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/pesanan-saya', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/akun', '/pesanan-saya');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/pemesanan', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
    Route::post('/pesanan/{transaksiSewa}/pembayaran', [DashboardController::class, 'pay'])->name('payments.store');
    Route::post('/pesanan/{transaksiSewa}/pengembalian', [DashboardController::class, 'requestReturn'])->name('returns.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/ekspor/penyewaan', [AdminController::class, 'exportRentals'])->name('rentals.export');
    Route::post('/kategori', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::post('/alat', [AdminController::class, 'storeEquipment'])->name('equipment.store');
    Route::patch('/alat/{peralatan}/status', [AdminController::class, 'toggleEquipment'])->name('equipment.toggle');
    Route::post('/unit', [AdminController::class, 'storeUnit'])->name('units.store');
    Route::patch('/sewa/{transaksiSewa}/status', [AdminController::class, 'updateRental'])->name('rentals.update');
    Route::patch('/pembayaran/{pembayaranSewa}', [AdminController::class, 'reviewPayment'])->name('payments.update');
    Route::get('/pembayaran/{pembayaranSewa}/bukti', [AdminController::class, 'downloadProof'])->name('payments.proof');
    Route::patch('/pengembalian/{pengembalianSewa}', [AdminController::class, 'completeReturn'])->name('returns.complete');
    Route::post('/servis', [AdminController::class, 'storeService'])->name('services.store');
    Route::patch('/servis/{servisUnit}/selesai', [AdminController::class, 'finishService'])->name('services.finish');
    Route::put('/konfigurasi', [AdminController::class, 'updateSettings'])->name('settings.update');
});
