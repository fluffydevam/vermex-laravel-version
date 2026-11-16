<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InventoryCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
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

        return view('admin.inventory.index', [
            'items' => $items,
            'stats' => $stats,
            'logs' => $logs,
            'search' => $search,
            'category' => $category,
            'stock' => $stock,
            'categories' => InventoryCategory::cases(),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', InventoryItem::class);

        return view('admin.inventory.create');
    }

    public function store(StoreInventoryItemRequest $request)
    {
        Gate::authorize('create', InventoryItem::class);

        $item = InventoryItem::create($request->validated());

        $this->inventory->recordOpeningStock($item, $request->user());

        return redirect()
            ->route('admin.inventory')
            ->with('success', "Inventory item '{$item->item_name}' created.");
    }

    public function edit(InventoryItem $inventoryItem)
    {
        Gate::authorize('update', $inventoryItem);

        $logs = $inventoryItem->logs()
            ->with('performedBy')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.inventory.edit', [
            'item' => $inventoryItem,
            'logs' => $logs,
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem)
    {
        Gate::authorize('update', $inventoryItem);

        $inventoryItem->update($request->validated());

        return redirect()
            ->route('admin.inventory')
            ->with('success', "Inventory item '{$inventoryItem->item_name}' updated.");
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
