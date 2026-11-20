<?php

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
    Route::view('/profile', 'profile.show')->name('profile.show');
    // فورم تعديل الحساب (من Breeze)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
