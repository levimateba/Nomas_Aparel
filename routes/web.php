<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\PriceController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\NewsEventController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
    Route::post('/blog/{slug}/comments', [BlogController::class, 'comment'])->name('blog.comment');
    Route::post('/contact', [HomeController::class, 'contact'])->name('contact.submit');

    // User Authentication Routes
    Route::get('/register', [AuthController::class, 'showRegister'])->middleware('guest')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('guest')->name('register.submit');
    Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::prefix('admin')->name('admin.')->middleware(App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/settings', [DashboardController::class, 'update'])->name('settings.update');
        Route::resource('about', AboutController::class)->names('about');
        Route::resource('services', ServiceController::class)->names('service');
        Route::resource('contacts', ContactController::class)->names('contact');
        Route::resource('blog', BlogPostController::class)->names('blog');
        Route::resource('prices', PriceController::class)->names('price');
        Route::resource('gallery', GalleryController::class)->except(['show'])->names('gallery');
        Route::resource('news-events', NewsEventController::class)->except(['show'])->names('news-events');
        Route::resource('videos', VideoController::class)->except(['show'])->names('video');
        Route::resource('team', TeamMemberController::class)->except(['show'])->names('team');
        Route::resource('users', UserController::class)->except(['show'])->names('users');
        Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update');
        Route::resource('roles', RoleController::class)->except(['show'])->names('roles');
        Route::resource('permissions', PermissionController::class)->except(['show'])->names('permissions');
        Route::resource('user-groups', UserGroupController::class)->except(['show'])->names('user-groups');
    });
});
