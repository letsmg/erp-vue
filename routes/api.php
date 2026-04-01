<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */
    
    Route::post('/login', [LoginController::class, 'login'])->name('api.login');
    
    /*
    |--------------------------------------------------------------------------
    | CLIENTS
    |--------------------------------------------------------------------------
    */
    
    Route::apiResource('clients', ClientController::class);
    
    Route::prefix('clients')->group(function () {
        Route::post('/validate-document', [ClientController::class, 'validateDocument'])
            ->name('clients.validate-document');
            
        Route::get('/search', [ClientController::class, 'search'])
            ->name('clients.search');
            
        Route::post('/{client}/toggle-status', [ClientController::class, 'toggleStatus'])
            ->name('clients.toggle-status');
            
        Route::get('/export', [ClientController::class, 'export'])
            ->name('clients.export');
    });
    
    /*
    |--------------------------------------------------------------------------
    | SHOPPING CART
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth:api')->prefix('shopping-cart')->group(function () {
        Route::get('/', [ShoppingCartController::class, 'index'])
            ->name('shopping-cart.index');
            
        Route::post('/', [ShoppingCartController::class, 'store'])
            ->name('shopping-cart.store');
            
        Route::get('/summary', [ShoppingCartController::class, 'summary'])
            ->name('shopping-cart.summary');
            
        Route::post('/shipping', [ShoppingCartController::class, 'shipping'])
            ->name('shopping-cart.shipping');
            
        Route::post('/checkout', [ShoppingCartController::class, 'checkout'])
            ->name('shopping-cart.checkout');
            
        Route::delete('/clear', [ShoppingCartController::class, 'clear'])
            ->name('shopping-cart.clear');
    });
    
    Route::middleware('auth:api')->prefix('shopping-cart')->group(function () {
        Route::put('/{cart_item}', [ShoppingCartController::class, 'update'])
            ->name('shopping-cart.update');
            
        Route::delete('/{cart_item}', [ShoppingCartController::class, 'destroy'])
            ->name('shopping-cart.destroy');
    });
    
    /*
    |--------------------------------------------------------------------------
    | PRODUCTS (API)
    |--------------------------------------------------------------------------
    */
    
    Route::apiResource('products', ProductController::class);
    
    Route::prefix('products')->group(function () {
        Route::get('/{product}/preview', [ProductController::class, 'preview'])
            ->name('products.preview');
            
        Route::patch('/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])
            ->name('products.toggle-featured');
            
        Route::patch('/{product}/toggle', [ProductController::class, 'toggle'])
            ->name('products.toggle');
    });
    
    /*
    |--------------------------------------------------------------------------
    | CATEGORIES (API)
    |--------------------------------------------------------------------------
    */
    
    Route::apiResource('categories', CategoryController::class);
    
    /*
    |--------------------------------------------------------------------------
    | SUPPLIERS (API)
    |--------------------------------------------------------------------------
    */
    
    Route::apiResource('suppliers', SupplierController::class);
    
    /*
    |--------------------------------------------------------------------------
    | USERS (API)
    |--------------------------------------------------------------------------
    */
    
    Route::apiResource('users', UserController::class);
    
    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED USER INFO
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth:api')->group(function () {
        Route::get('/me', function () {
            return response()->json([
                'success' => true,
                'data' => Auth::user(),
            ]);
        })->name('api.me');
        
        Route::post('/logout', function () {
            Auth::logout();
            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso.',
            ]);
        })->name('api.logout');
    });
});
