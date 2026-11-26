<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DealController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MyItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// الصفحة الرئيسية
Route::get('/', [HomeController::class, 'index'])->name('home');

// البحث والفلترة
Route::get('/search', [SearchController::class, 'index'])->name('search');

// تفاصيل الغرض
Route::get('/items/{item}', [ItemController::class, 'show'])->whereNumber('item')->name('items.show');

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

    // الطلبات: إرسال، إلغاء، طلباتي، الطلبات الواردة، تفاصيل العملية
    Route::post('/items/{item}/requests', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/incoming', [RequestController::class, 'incoming'])->name('requests.incoming');
    Route::get('/requests/{itemRequest}', [RequestController::class, 'show'])->whereNumber('itemRequest')->name('requests.show');
    Route::patch('/requests/{itemRequest}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');

    // مراحل العملية: قبول/رفض، الموعد، الاستلام، الإرجاع
    Route::patch('/requests/{itemRequest}/accept', [DealController::class, 'accept'])->name('requests.accept');
    Route::patch('/requests/{itemRequest}/reject', [DealController::class, 'reject'])->name('requests.reject');
    Route::patch('/requests/{itemRequest}/appointment/confirm', [DealController::class, 'confirmAppointment'])->name('requests.appointment.confirm');
    Route::post('/requests/{itemRequest}/appointment', [DealController::class, 'proposeAppointment'])->name('requests.appointment.propose');
    Route::patch('/requests/{itemRequest}/receive', [DealController::class, 'confirmReceipt'])->name('requests.receive');
    Route::patch('/requests/{itemRequest}/return', [DealController::class, 'reportReturn'])->name('requests.return');
    Route::patch('/requests/{itemRequest}/return/confirm', [DealController::class, 'confirmReturn'])->name('requests.return.confirm');
    Route::patch('/requests/{itemRequest}/return/dispute', [DealController::class, 'disputeReturn'])->name('requests.return.dispute');

    // التقييم المتبادل بعد انتهاء العملية
    Route::post('/requests/{itemRequest}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // الإبلاغ عن مخالفة
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    // الإشعارات وسجل التعاملات
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])->whereNumber('notification')->name('notifications.open');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
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

    // البلاغات
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}/resolve', [Admin\ReportController::class, 'resolve'])->name('reports.resolve');

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
