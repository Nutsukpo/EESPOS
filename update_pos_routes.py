from pathlib import Path

path = Path("routes/web.php")
content = path.read_text()

import_line = "use App\\Http\\Controllers\\ProductCategoryController;"
new_import = import_line + "\nuse App\\Http\\Controllers\\PosController;"

if "use App\\Http\\Controllers\\PosController;" not in content:
    if import_line not in content:
        raise SystemExit("Could not find controller import location.")
    content = content.replace(import_line, new_import, 1)

route_anchor = """                // Products
"""

new_routes = """                // Point of Sale
                Route::middleware('can:sales.create')->group(function () {
                    Route::get('/pos', [PosController::class, 'index'])
                        ->name('business.pos.index');

                    Route::get('/pos/products/search', [PosController::class, 'searchProducts'])
                        ->name('business.pos.products.search');
                });

                // Products
"""

if "business.pos.products.search" not in content:
    if route_anchor not in content:
        raise SystemExit("Could not find route insertion location.")
    content = content.replace(route_anchor, new_routes, 1)

path.write_text(content)
print("Successfully added POS routes to routes/web.php.")
