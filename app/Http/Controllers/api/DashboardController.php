<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas del dashboard
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Fechas
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        // Ventas totales
        $totalSales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->count();

        // Productos totales
        $totalProducts = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        // Ventas de hoy (monto)
        $todaySales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total');

        // Ventas de la semana (monto)
        $weeklySales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $startOfWeek)
            ->sum('total');

        // Ventas del mes (monto)
        $monthlySales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->sum('total');


        $salesByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $daySales = Sale::where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->whereDate('created_at', $date->toDateString())
                ->sum('total');

            $dayCount = Sale::where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->whereDate('created_at', $date->toDateString())
                ->count();

            $salesByDay[] = [
                'date' => $date->toDateString(),
                'total' => floatval($daySales),
                'count' => $dayCount,
                'label' => $date->locale('es')->isoFormat('dddd')
            ];
        }


        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.status', 'paid')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();


        $recentSales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'folio' => $sale->folio,
                    'customer_name' => $sale->customer_name ?? 'Cliente general',
                    'total' => floatval($sale->total),
                    'items_count' => $sale->items->count(),
                    'created_at' => $sale->created_at->toISOString(),
                    'status' => $sale->status
                ];
            });

        // Ventas por método de pago
        $paymentMethods = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method,
                    'count' => $item->count,
                    'total' => floatval($item->total)
                ];
            });

        // Totales del día (para caja)
        $todayCashSales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->where('payment_method', 'cash')
            ->whereDate('created_at', $today)
            ->sum('total');

        $todayCardSales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->where('payment_method', 'card')
            ->whereDate('created_at', $today)
            ->sum('total');

        $todayTransferSales = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->where('payment_method', 'transfer')
            ->whereDate('created_at', $today)
            ->sum('total');

        // Monto total de ventas
        $totalRevenue = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->sum('total');

        return response()->json([
            'stats' => [
                'total_sales' => $totalSales,
                'total_products' => $totalProducts,
                'total_revenue' => floatval($totalRevenue),
                'today_sales' => floatval($todaySales),
                'weekly_sales' => floatval($weeklySales),
                'monthly_sales' => floatval($monthlySales),
            ],
            'sales_by_day' => $salesByDay,
            'top_products' => $topProducts,
            'recent_sales' => $recentSales,
            'payment_methods' => $paymentMethods,
            'today_payment_breakdown' => [
                'cash' => floatval($todayCashSales),
                'card' => floatval($todayCardSales),
                'transfer' => floatval($todayTransferSales),
            ]
        ]);
    }
}
