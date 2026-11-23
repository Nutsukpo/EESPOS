<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user();

        abort_unless($business && $user, 403);

        $isOwner = (int) $business->owner_id === (int) $user->id;

        $branches = $business->branches()
            ->where('is_active', true)
            ->when(
                !$isOwner,
                fn ($query) => $query->whereIn(
                    'branches.id',
                    $user->branches()
                        ->where('branches.business_id', $business->id)
                        ->where('branches.is_active', true)
                        ->select('branches.id')
                )
            )
            ->get(['id', 'name']);

        $branchIds = $branches->pluck('id');

        $salesQuery = Sale::query()
            ->where('business_id', $business->id)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed');

        $todaySales = (clone $salesQuery)
            ->whereDate('sold_at', today())
            ->sum('total_amount');

        $monthlyRevenue = (clone $salesQuery)
            ->whereBetween('sold_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->sum('total_amount');

        $activeProducts = Product::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->count();

        $inventory = \DB::table('branch_inventories')
            ->where('business_id', $business->id)
            ->whereIn('branch_id', $branchIds);

        $inventoryTotals = [
            'total' => (clone $inventory)->count(),
            'in_stock' => (clone $inventory)
                ->whereRaw('quantity > GREATEST(minimum_stock, reorder_level)')
                ->count(),
            'low_stock' => (clone $inventory)
                ->whereRaw('quantity > 0 AND quantity <= GREATEST(minimum_stock, reorder_level)')
                ->count(),
            'out_of_stock' => (clone $inventory)
                ->where('quantity', '<=', 0)
                ->count(),
        ];

        $salesTrend = (clone $salesQuery)
            ->whereBetween('sold_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->selectRaw('DATE(sold_at) as sale_date, SUM(total_amount) as total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        $recentSales = Sale::query()
            ->with(['customer:id,name'])
            ->where('business_id', $business->id)
            ->whereIn('branch_id', $branchIds)
            ->latest('sold_at')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'business' => $business,
            'branches' => $branches,
            'todaySales' => $todaySales,
            'monthlyRevenue' => $monthlyRevenue,
            'activeProducts' => $activeProducts,
            'inventoryTotals' => $inventoryTotals,
            'salesTrend' => $salesTrend,
            'recentSales' => $recentSales,
        ]);
    }
}
