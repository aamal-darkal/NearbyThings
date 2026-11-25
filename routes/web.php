<?php

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
});

require __DIR__.'/auth.php';
