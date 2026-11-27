
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
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route('dashboard');
})->name('home');



// Main authenticated dashboard redirect
Route::get('/dashboard', function () {

    $user = auth()->user();

    // Find an active business membership first.
    $business = $user->businesses()
        ->wherePivot('status', 'active')
        ->first();

    // Otherwise, check the user's active owned business.
    if (!$business) {
        $business = $user->ownedBusinesses()
            ->where('is_active', true)
            ->first();
    }

    if (!$business) {
        return redirect()->route('business.create');
    }

    // Check the user's role within this business.
    $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
    $previousTeamId = $registrar->getPermissionsTeamId();

    try {
        $registrar->setPermissionsTeamId($business->id);

        // Clear cached role relationships before checking.
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $isCashier = $user->hasRole('Cashier');
    } finally {
        $registrar->setPermissionsTeamId($previousTeamId);
    }

    // Cashiers go directly to the Point of Sale.
    if ($isCashier) {
        return redirect()->route('business.pos.index', [
            'business' => $business->slug,
        ]);
    }

    // Other business users go to the dashboard.
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

                    Route::post('/pos/lock', [\App\Http\Controllers\PosSessionController::class, 'lock'])
                        ->name('business.pos.lock');

                    Route::post('/pos/activity', [\App\Http\Controllers\PosSessionController::class, 'activity'])
                        ->name('business.pos.activity');

                    Route::post('/pos/unlock', [\App\Http\Controllers\PosSessionController::class, 'unlock'])
                        ->middleware('throttle:5,1')
                        ->name('business.pos.unlock');

                    Route::get('/pos/products/search', [PosController::class, 'searchProducts'])
                        ->middleware(\App\Http\Middleware\EnsurePosUnlocked::class)
                        ->name('business.pos.products.search');

                    Route::post('/pos/checkout', [PosController::class, 'checkout'])
                        ->middleware(\App\Http\Middleware\EnsurePosUnlocked::class)
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
