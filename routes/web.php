<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ClientAuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController; // ✅ ADICIONADO
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| 1. VITRINE (PÚBLICO)
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

Route::get('/', [StoreController::class, 'index'])->name('store.index');

// ✅ STORE USA SLUG
Route::get('/store/product/{product:slug}', [StoreController::class, 'show'])
    ->name('store.product');

// (opcional futuro)
// Route::get('/store/category/{category:slug}', ...);

Route::post('/terms/accept', [StoreController::class, 'acceptTerms'])
    ->name('store.terms.accept');

/*
|--------------------------------------------------------------------------
| 2. AUTH (GUEST)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');    
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    Route::get('/forgot-password', [LoginController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('/forgot-password', [LoginController::class, 'sendResetLinkEmail'])
        ->name('password.email');
});

/*
|--------------------------------------------------------------------------
| 2.1. CLIENT AUTH (GUEST)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->prefix('cliente')->name('client.')->group(function () {
    Route::get('/login', [ClientAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClientAuthController::class, 'login'])->name('login.post');
    Route::get('/registrar', [ClientAuthController::class, 'showRegister'])->name('register');
    Route::post('/registrar', [ClientAuthController::class, 'register'])->name('register.post');
    Route::get('/esqueci-senha', [ClientAuthController::class, 'showForgotPassword'])->name('forgot.password');
    Route::post('/esqueci-senha', [ClientAuthController::class, 'sendResetLinkEmail'])->name('forgot.password.post');
});

/*
|--------------------------------------------------------------------------
| 3. ADMIN (AUTH)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    // 👁️ PREVIEW (FORÇANDO ID)
    Route::get('/products/{product:id}/preview', [ProductController::class, 'preview'])
        ->name('products.preview');

    // ⭐ FEATURED (FORÇANDO ID)
    Route::patch('/products/{product:id}/toggle-featured', [ProductController::class, 'toggleFeatured'])
        ->name('products.toggle-featured');

    // 🔥 ATIVAR / DESATIVAR (IMPORTANTE: também forçar ID)
    Route::patch('/products/{product:id}/toggle', [ProductController::class, 'toggle'])
        ->name('products.toggle');

    // CRUD
    Route::resource('products', ProductController::class);

    /*
    |--------------------------------------------------------------------------
    | CATEGORIES ✅ (NOVO)
    |--------------------------------------------------------------------------
    */

    // 🔥 PADRÃO ADMIN = ID (recomendado)
    Route::resource('categories', CategoryController::class);

    /*
    |--------------------------------------------------------------------------
    | OUTROS
    |--------------------------------------------------------------------------
    */

    Route::resource('suppliers', SupplierController::class);

    /*
    |--------------------------------------------------------------------------
    | RELATÓRIOS
    |--------------------------------------------------------------------------
    */

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');

    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

    Route::resource('users', UserController::class);    

    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset');

    Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])
        ->name('users.toggle');

    /*
    |--------------------------------------------------------------------------
    | CLIENTS (ADMIN)
    |--------------------------------------------------------------------------
    */

    Route::resource('clients', ClientController::class);
    Route::get('/clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('clients.toggle.status');
    Route::post('/clients/validate-document', [ClientController::class, 'validateDocument'])->name('clients.validate.document');
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::get('/clients/export', [ClientController::class, 'export'])->name('clients.export');

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        // rotas exclusivas
    });
});

/*
|--------------------------------------------------------------------------
| 4. CLIENT AREA (AUTH)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'client'])->prefix('cliente')->name('client.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Client/Dashboard'))->name('dashboard');
    Route::get('/meus-dados', [ClientController::class, 'showClientData'])->name('profile');
    Route::put('/meus-dados', [ClientController::class, 'updateClientData'])->name('profile.update');
    Route::post('/logout', [ClientAuthController::class, 'logout'])->name('logout');
});