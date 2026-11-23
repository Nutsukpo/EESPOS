<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user();

        abort_unless($business, 403);
        abort_unless(
            $user->can('sales.view.all') || $user->can('sales.view.own'),
            403,
            'You do not have permission to view sales history.'
        );

        $isOwner = (int) $business->owner_id === (int) $user->id;
        $canViewAll = $user->can('sales.view.all');

        $branches = $business->branches()
            ->where('is_active', true)
            ->when(
                !$isOwner,
                fn ($query) => $query->whereIn(
                    'id',
                    $user->branches()
                        ->where('branches.business_id', $business->id)
                        ->where('branches.is_active', true)
                        ->select('branches.id')
                )
            )
            ->orderBy('name')
            ->get();

        $sales = Sale::query()
            ->with(['branch:id,name', 'customer:id,name', 'cashier:id,name'])
            ->where('business_id', $business->id)
            ->when(!$canViewAll, fn ($query) => $query->where('cashier_id', $user->id))
            ->when(
                !$isOwner,
                fn ($query) => $query->whereIn('branch_id', $branches->pluck('id'))
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) =>
                            $customerQuery->where('name', 'like', "%{$search}%")
                        )
                        ->orWhereHas('cashier', fn ($cashierQuery) =>
                            $cashierQuery->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->when($request->filled('branch_id'), fn ($query) =>
                $query->where('branch_id', $request->integer('branch_id'))
            )
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->input('status'))
            )
            ->when($request->filled('payment_status'), fn ($query) =>
                $query->where('payment_status', $request->input('payment_status'))
            )
            ->when($request->filled('date_from'), fn ($query) =>
                $query->whereDate('sold_at', '>=', $request->input('date_from'))
            )
            ->when($request->filled('date_to'), fn ($query) =>
                $query->whereDate('sold_at', '<=', $request->input('date_to'))
            )
            ->latest('sold_at')
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', [
            'business' => $business,
            'branches' => $branches,
            'sales' => $sales,
        ]);
    }

    public function show(Request $request, \App\Models\Business $businessRoute, int $sale): View
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user();

        abort_unless($business, 403);
        abort_unless(
            $user->can('sales.view.all') || $user->can('sales.view.own'),
            403,
            'You do not have permission to view sales.'
        );

        $query = Sale::query()
            ->with([
                'branch:id,name',
                'customer:id,name,phone',
                'cashier:id,name',
                'items',
                'payments',
            ])
            ->where('business_id', $business->id);

        if (!$user->can('sales.view.all')) {
            $query->where('cashier_id', $user->id);
        }

        if ((int) $business->owner_id !== (int) $user->id) {
            $assignedBranchIds = $user->branches()
                ->where('branches.business_id', $business->id)
                ->where('branches.is_active', true)
                ->select('branches.id');

            $query->whereIn('branch_id', $assignedBranchIds);
        }

        $saleRecord = $query->findOrFail($sale);

        return view('sales.show', [
            'business' => $business,
            'sale' => $saleRecord,
        ]);
    }
}
