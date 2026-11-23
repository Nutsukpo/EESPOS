
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessUserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SalesHistoryController;

// ============================================
// EES POS - Homepage
// ============================================

Route::get('/', function () {

    // Redirect visitors to login.
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    // Find an active business membership.
    $business = $user->businesses()
        ->wherePivot('status', 'active')
        ->first();

    // Check owned businesses if no membership was found.
    if (!$business) {
        $business = $user->ownedBusinesses()
            ->where('is_active', true)
            ->first();
    }

    // Redirect to the business dashboard.
    if ($business) {
        return redirect()->route('business.dashboard', [
            'business' => $business->slug,
        ]);
    }

    // User has no business yet.
    return redirect()->route('business.create');

})->name('home');



// Main authenticated dashboard redirect
Route::get('/dashboard', function () {

    $user = auth()->user();

    // Find a business associated with this user.
    $business = $user->businesses()->first()
        ?? $user->ownedBusinesses()->first();

    // If the user has no business, open business registration.
    if (!$business) {
        return redirect()->route('business.create');
    }

    // Otherwise, open the business dashboard.
    return redirect()->route('business.dashboard', [
        'business' => $business->slug,
    ]);

})->middleware('auth')->name('dashboard');



// ============================================
// EES POS - Authenticated Application Routes
// ============================================

Route::middleware(['auth'])
    ->prefix('app')
    ->group(function () {

        // ========================================
        // Business Registration
        // ========================================

        Route::get('/business/create', [
            BusinessController::class,
            'create'
        ])->name('business.create');

        Route::post('/business', [
            BusinessController::class,
            'store'
        ])->name('business.store');


        // ========================================
        // Business Dashboard
        // ========================================

        Route::middleware(['business.access'])
            ->prefix('{business:slug}')
            ->group(function () {

                // Business Profile
                Route::get('/profile', [\App\Http\Controllers\BusinessProfileController::class, 'show'])
                    ->name('business.profile.show');

                Route::get('/profile/edit', [\App\Http\Controllers\BusinessProfileController::class, 'edit'])
                    ->name('business.profile.edit');

                Route::put('/profile', [\App\Http\Controllers\BusinessProfileController::class, 'update'])
                    ->name('business.profile.update');

                Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
                    ->name('business.dashboard');

                // Stock Management
                Route::get('/stock', [\App\Http\Controllers\StockManagementController::class, 'index'])
                    ->name('business.stock.index');

                Route::post('/stock/receive', [\App\Http\Controllers\StockManagementController::class, 'receive'])
                    ->name('business.stock.receive');

                Route::post('/stock/adjust', [\App\Http\Controllers\StockManagementController::class, 'adjust'])
                    ->name('business.stock.adjust');

                // Point of Sale
                Route::middleware('can:sales.create')->group(function () {
                    Route::get('/pos', [PosController::class, 'index'])
                        ->name('business.pos.index');

                    Route::get('/pos/products/search', [PosController::class, 'searchProducts'])
                        ->name('business.pos.products.search');

                    Route::post('/pos/checkout', [PosController::class, 'checkout'])
                        ->name('business.pos.checkout');
                });

                // Sales History
                Route::get('/sales', [SalesHistoryController::class, 'index'])
                    ->name('business.sales.index');

                Route::get('/sales/{sale}', [SalesHistoryController::class, 'show'])
                    ->name('business.sales.show');

                // Products
                Route::get('/products', [ProductController::class, 'index'])
                    ->name('business.products.index');

                Route::get('/products/create', [ProductController::class, 'create'])
                    ->name('business.products.create');

                Route::post('/products', [ProductController::class, 'store'])
                    ->name('business.products.store');

                Route::get('/products/{product}', [ProductController::class, 'show'])
                    ->name('business.products.show');

                Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
                    ->name('business.products.edit');

                Route::put('/products/{product}', [ProductController::class, 'update'])
                    ->name('business.products.update');

                Route::delete('/products/{product}', [ProductController::class, 'destroy'])
                    ->name('business.products.destroy');

                // Product Categories
                Route::get('/categories', [ProductCategoryController::class, 'index'])
                    ->name('business.categories.index');

                Route::get('/categories/create', [ProductCategoryController::class, 'create'])
                    ->name('business.categories.create');

                Route::post('/categories', [ProductCategoryController::class, 'store'])
                    ->name('business.categories.store');

                Route::get('/categories/{category}', [ProductCategoryController::class, 'show'])
                    ->name('business.categories.show');

                Route::get('/categories/{category}/edit', [ProductCategoryController::class, 'edit'])
                    ->name('business.categories.edit');

                Route::put('/categories/{category}', [ProductCategoryController::class, 'update'])
                    ->name('business.categories.update');

                Route::delete('/categories/{category}', [ProductCategoryController::class, 'destroy'])
                    ->name('business.categories.destroy');

                Route::get('/users/create', [
                    BusinessUserController::class,
                    'create'
                ])->name('business.users.create');

                Route::post('/users', [
                    BusinessUserController::class,
                    'store'
                ])->name('business.users.store');

                Route::get('/users', [
                    BusinessUserController::class,
                    'index'
                ])->name('business.users.index');

                Route::get('/users/{user}', [
                    BusinessUserController::class,
                    'show'
                ])->name('business.users.show');

                Route::get('/users/{user}/edit', [
                    BusinessUserController::class,
                    'edit'
                ])->name('business.users.edit');

                Route::put('/users/{user}', [
                    BusinessUserController::class,
                    'update'
                ])->name('business.users.update');

                Route::delete('/users/{user}', [
                    BusinessUserController::class,
                    'destroy'
                ])->name('business.users.destroy');

            });

    });
