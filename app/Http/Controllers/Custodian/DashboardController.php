<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('view-any-inventory');

        $stats = [
            'items' => InventoryItem::count(),
            'low_stock' => InventoryItem::whereColumn('quantity_in_stock', '<=', 'min_threshold')
                ->where('quantity_in_stock', '>', 0)
                ->count(),
            'out_of_stock' => InventoryItem::where('quantity_in_stock', '<=', 0)->count(),
            'adjustments_month' => InventoryLog::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        $lowStockItems = InventoryItem::query()
            ->whereColumn('quantity_in_stock', '<=', 'min_threshold')
            ->orderBy('quantity_in_stock')
            ->limit(8)
            ->get();

        return view('custodian.dashboard', [
            'stats' => $stats,
            'lowStockItems' => $lowStockItems,
        ]);
    }
}
