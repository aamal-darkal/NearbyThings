<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MyItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// الصفحة الرئيسية
Route::view('/', 'home')->name('home');

// تفاصيل الغرض (حالياً نفس الصفحة لكل الأغراض)
Route::view('/items/{id}', 'items.show')->whereNumber('id')->name('items.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // صفحة الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    // فورم تعديل الحساب (من Breeze)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // إعلاناتي: إضافة وتعديل وحذف وإيقاف مؤقت
    Route::get('/my-items', [MyItemController::class, 'index'])->name('items.mine');
    Route::get('/items/create', [MyItemController::class, 'create'])->name('items.create');
    Route::post('/items', [MyItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [MyItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [MyItemController::class, 'update'])->name('items.update');
    Route::patch('/items/{item}/toggle', [MyItemController::class, 'toggle'])->name('items.toggle');
    Route::delete('/items/{item}', [MyItemController::class, 'destroy'])->name('items.destroy');

    // المفضلة
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{item}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

// ===== لوحة المشرف =====
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // الإعلانات والطلبات + حجب الأغراض
    Route::get('/listings', [Admin\ListingController::class, 'index'])->name('listings.index');
    Route::patch('/items/{item}/block', [Admin\ListingController::class, 'block'])->name('items.block');
    Route::patch('/items/{item}/unblock', [Admin\ListingController::class, 'unblock'])->name('items.unblock');

    // التصنيفات وحالات الغرض
    Route::get('/categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{category}/toggle', [Admin\CategoryController::class, 'toggle'])->name('categories.toggle');
    Route::delete('/categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/conditions', [Admin\CategoryController::class, 'storeCondition'])->name('conditions.store');
    Route::put('/conditions/{condition}', [Admin\CategoryController::class, 'updateCondition'])->name('conditions.update');
    Route::delete('/conditions/{condition}', [Admin\CategoryController::class, 'destroyCondition'])->name('conditions.destroy');

    // اقتراحات التصنيفات
    Route::get('/suggestions', [Admin\SuggestionController::class, 'index'])->name('suggestions.index');
    Route::patch('/suggestions/{category}/approve', [Admin\SuggestionController::class, 'approve'])->name('suggestions.approve');
    Route::patch('/suggestions/{category}/move', [Admin\SuggestionController::class, 'move'])->name('suggestions.move');

    // المستخدمون
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/ban', [Admin\UserController::class, 'ban'])->name('users.ban');
    Route::patch('/users/{user}/unban', [Admin\UserController::class, 'unban'])->name('users.unban');
});

require __DIR__.'/auth.php';
