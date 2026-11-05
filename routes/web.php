<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\BorrowOrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Admin\BorrowOrderController as AdminBorrowOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\BankStatementController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sach', [BookController::class, 'index'])->name('books.index');
Route::get('/sach/{book}', [BookController::class, 'show'])->name('books.show');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.store');
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/ho-so', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/ho-so', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/ho-so/mat-khau', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/yeu-thich', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/yeu-thich/{book}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/thanh-toan/{payment}', [PaymentController::class, 'show'])->name('my-payments.show');
    Route::post('/thanh-toan/{payment}/xac-nhan', [PaymentController::class, 'submit'])->name('my-payments.submit');
    Route::get('/don-muon-thue', [BorrowOrderController::class, 'index'])->name('my-orders.index');
    Route::post('/sach/{book}/dat-muon-thue', [BorrowOrderController::class, 'store'])->name('orders.store');
    Route::patch('/don-muon-thue/{order}/huy', [BorrowOrderController::class, 'cancel'])->name('my-orders.cancel');
    Route::post('/sach/{book}/danh-gia', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/danh-gia/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::get('/thong-bao', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/thong-bao/doc-tat-ca', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::patch('/thong-bao/{notification}/doc', [NotificationController::class, 'read'])->name('notifications.read');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('books', AdminBookController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('authors', AdminAuthorController::class)->except(['show']);
    Route::get('/orders', [AdminBorrowOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminBorrowOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/approve', [AdminBorrowOrderController::class, 'approve'])->name('orders.approve');
    Route::patch('/orders/{order}/reject', [AdminBorrowOrderController::class, 'reject'])->name('orders.reject');
    Route::patch('/orders/{order}/return', [AdminBorrowOrderController::class, 'returnBook'])->name('orders.return');
    Route::patch('/orders/{order}/items/{item}/return', [AdminBorrowOrderController::class, 'returnItem'])->name('orders.items.return');
    Route::patch('/orders/{order}/items/{item}/extend', [AdminBorrowOrderController::class, 'extendDueDate'])->name('orders.items.extend');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/bank-statements', [BankStatementController::class, 'index'])->name('bank-statements.index');
    Route::post('/bank-statements/upload', [BankStatementController::class, 'upload'])->name('bank-statements.upload');
    Route::patch('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
    Route::patch('/payments/{payment}/complete', [AdminPaymentController::class, 'complete'])->name('payments.complete');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->name('users.status');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
});
