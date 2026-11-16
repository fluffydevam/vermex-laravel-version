<?php

namespace App\Http\Controllers\Custodian;

use App\Enums\InventoryCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('view-any-inventory');

        $search = trim((string) $request->input('search', ''));
        $category = (string) $request->input('category', '');
        $stock = (string) $request->input('stock', '');

        $items = InventoryItem::query()
            ->when($search !== '', fn ($q) => $q->where('item_name', 'like', "%{$search}%"))
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->when($stock === 'low', fn ($q) => $q->whereColumn('quantity_in_stock', '<=', 'min_threshold')->where('quantity_in_stock', '>', 0))
            ->when($stock === 'out', fn ($q) => $q->where('quantity_in_stock', '<=', 0))
            ->when($stock === 'in', fn ($q) => $q->whereColumn('quantity_in_stock', '>', 'min_threshold'))
            ->orderBy('item_name')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'items' => InventoryItem::count(),
            'low' => InventoryItem::whereColumn('quantity_in_stock', '<=', 'min_threshold')->where('quantity_in_stock', '>', 0)->count(),
            'out' => InventoryItem::where('quantity_in_stock', '<=', 0)->count(),
            'value' => (float) InventoryItem::query()->selectRaw('COALESCE(SUM(quantity_in_stock * unit_cost), 0) as total')->value('total'),
        ];

        $logs = InventoryLog::query()
            ->with(['inventoryItem', 'performedBy'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('custodian.inventory.index', [
            'items' => $items,
            'stats' => $stats,
            'logs' => $logs,
            'search' => $search,
            'category' => $category,
            'stock' => $stock,
            'categories' => InventoryCategory::cases(),
        ]);
    }

    public function adjust(AdjustStockRequest $request, InventoryItem $inventoryItem)
    {
        Gate::authorize('adjust', $inventoryItem);

        try {
            $item = $this->inventory->adjust(
                $inventoryItem,
                $request->string('adjustment_type')->toString(),
                (float) $request->input('adjust_amount'),
                $request->user(),
                $request->filled('remarks') ? $request->string('remarks')->toString() : null,
            );
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return back()->withErrors(['adjust_amount' => $e->getMessage()])->withInput();
        }

        return back()->with(
            'success',
            "Stock for '{$item->item_name}' is now " . number_format((float) $item->quantity_in_stock, 2) . " {$item->unit}."
        );
    }
}
