<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Apply a stock movement and write the matching audit log inside one transaction.
     *
     * @param  string  $type  add|subtract|set
     */
    public function adjust(InventoryItem $item, string $type, float $amount, User $user, ?string $remarks = null): InventoryItem
    {
        return DB::transaction(function () use ($item, $type, $amount, $user, $remarks) {
            /** @var InventoryItem $locked */
            $locked = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);

            $current = (float) $locked->quantity_in_stock;

            [$newQuantity, $delta, $action] = match ($type) {
                'add' => [$current + $amount, $amount, 'Stock Added'],
                'subtract' => $this->subtract($current, $amount),
                'set' => [$amount, $amount - $current, 'Stock Audit Set'],
                default => throw new \InvalidArgumentException("Unknown adjustment type [{$type}]."),
            };

            if ($newQuantity < 0) {
                throw new InsufficientStockException('Stock cannot go below zero.');
            }

            $locked->update(['quantity_in_stock' => $newQuantity]);

            InventoryLog::create([
                'inventory_item_id' => $locked->id,
                'performed_by' => $user->id,
                'action_type' => $action,
                'quantity_changed' => $delta,
                'remarks' => $remarks ?: 'Manual stock adjustment',
            ]);

            return $locked;
        });
    }

    /**
     * Deduct stock for a completed field job and record both the material line and audit log.
     */
    public function consumeForJob(InventoryItem $item, float $quantity, User $user, JobOrder $job, ?string $remarks = null): InventoryItem
    {
        return DB::transaction(function () use ($item, $quantity, $user, $job, $remarks) {
            /** @var InventoryItem $locked */
            $locked = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);

            if ($quantity > (float) $locked->quantity_in_stock) {
                throw new InsufficientStockException(
                    "Not enough {$locked->item_name} in stock (" . number_format((float) $locked->quantity_in_stock, 2) . " {$locked->unit})."
                );
            }

            $locked->update([
                'quantity_in_stock' => (float) $locked->quantity_in_stock - $quantity,
            ]);

            $job->materials()->create([
                'inventory_item_id' => $locked->id,
                'quantity_used' => $quantity,
                'unit_used' => $locked->unit,
            ]);

            InventoryLog::create([
                'inventory_item_id' => $locked->id,
                'performed_by' => $user->id,
                'job_order_id' => $job->id,
                'action_type' => 'Job Order Deduction',
                'quantity_changed' => -$quantity,
                'remarks' => $remarks ?: "Materials used on job order #{$job->id}",
            ]);

            return $locked;
        });
    }

    /**
     * Record the opening balance for a newly created item.
     */
    public function recordOpeningStock(InventoryItem $item, User $user): void
    {
        $quantity = (float) $item->quantity_in_stock;

        if ($quantity <= 0) {
            return;
        }

        InventoryLog::create([
            'inventory_item_id' => $item->id,
            'performed_by' => $user->id,
            'action_type' => 'Initial Stock',
            'quantity_changed' => $quantity,
            'remarks' => 'New item added to inventory',
        ]);
    }

    /**
     * @return array{0: float, 1: float, 2: string}
     */
    private function subtract(float $current, float $amount): array
    {
        if ($amount > $current) {
            throw new InsufficientStockException(
                'Cannot deduct more than the current stock (' . number_format($current, 2) . ').'
            );
        }

        return [$current - $amount, -$amount, 'Stock Deducted'];
    }
}
