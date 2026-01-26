<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryReportController extends Controller
{
    /**
     * Display inventory reports.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Summary statistics
        $totalItems = InventoryItem::active()->count();
        $lowStockItems = InventoryItem::lowStock()->active()->count();
        $totalStockValue = InventoryItem::active()->get()->sum('stock_value');

        // Stock movements summary
        $stockInCount = StockMovement::stockIn()
            ->dateRange($startDate, $endDate)
            ->count();
        
        $stockOutCount = StockMovement::stockOut()
            ->dateRange($startDate, $endDate)
            ->count();
        
        $usageCount = StockMovement::usage()
            ->dateRange($startDate, $endDate)
            ->count();

        $stockInValue = StockMovement::stockIn()
            ->dateRange($startDate, $endDate)
            ->sum('total_cost');
        
        $stockOutValue = StockMovement::stockOut()
            ->dateRange($startDate, $endDate)
            ->sum('total_cost');
        
        $usageValue = StockMovement::usage()
            ->dateRange($startDate, $endDate)
            ->sum('total_cost');

        // Items with movements in date range
        $itemsWithMovements = InventoryItem::with(['stockMovements' => function($query) use ($startDate, $endDate) {
            $query->dateRange($startDate, $endDate);
        }])
        ->whereHas('stockMovements', function($query) use ($startDate, $endDate) {
            $query->dateRange($startDate, $endDate);
        })
        ->active()
        ->get();

        // Low stock items
        $lowStockList = InventoryItem::lowStock()->active()->orderBy('current_stock')->get();

        return view('inventory.reports.index', compact(
            'startDate',
            'endDate',
            'totalItems',
            'lowStockItems',
            'totalStockValue',
            'stockInCount',
            'stockOutCount',
            'usageCount',
            'stockInValue',
            'stockOutValue',
            'usageValue',
            'itemsWithMovements',
            'lowStockList'
        ));
    }

    /**
     * Export inventory report to CSV.
     */
    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $movements = StockMovement::with(['inventoryItem', 'creator'])
            ->dateRange($startDate, $endDate)
            ->orderBy('movement_date')
            ->get();

        $filename = "inventory_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Date',
                'Item',
                'Type',
                'Quantity',
                'Unit',
                'Unit Cost',
                'Total Cost',
                'Balance After',
                'Notes',
                'Created By',
            ]);

            // Data rows
            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement->movement_date->format('Y-m-d'),
                    $movement->inventoryItem->name,
                    $movement->type_name,
                    number_format($movement->quantity, 2),
                    $movement->inventoryItem->unit,
                    number_format($movement->unit_cost ?? 0, 2),
                    number_format($movement->total_cost ?? 0, 2),
                    number_format($movement->balance_after, 2),
                    $movement->notes ?? '',
                    $movement->creator->name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
