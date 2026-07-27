<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
class CashRegisterController extends Controller
{
    /**
     * Obtener caja actual o crear una nueva
     */
    public function current()
    {
        $cashRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'open')
            ->latest()
            ->first();

        return response()->json($cashRegister);
    }

    /**
     * Abrir caja
     */
    public function open(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255'
        ]);

        // Verificar si ya hay una caja abierta
        $openRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'open')
            ->first();

        if ($openRegister) {
            return response()->json([
                'message' => 'Ya hay una caja abierta',
                'cash_register' => $openRegister
            ], 400);
        }

        $cashRegister = CashRegister::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'opening_balance' => $request->opening_balance,
            'opened_at' => now(),
            'status' => 'open',
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Caja abierta correctamente',
            'cash_register' => $cashRegister
        ], 201);
    }

    /**
     * Cerrar caja
     */
/**
 * Cerrar caja
 */
public function close(Request $request)
{
    $request->validate([
        'closing_balance' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:255'
    ]);

    $cashRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)
        ->where('status', 'open')
        ->first();

    if (!$cashRegister) {
        return response()->json([
            'message' => 'No hay una caja abierta'
        ], 404);
    }

    // CALCULAR VENTAS DEL PERÍODO
    $salesData = Sale::where('tenant_id', Auth::user()->tenant_id)
        ->where('cash_register_id', $cashRegister->id)
        ->whereBetween('created_at', [$cashRegister->opened_at, now()])
        ->where('status', 'paid')
        ->select(
            DB::raw('COALESCE(SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END), 0) as cash_sales'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_method = "card" THEN total ELSE 0 END), 0) as card_sales'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_method = "transfer" THEN total ELSE 0 END), 0) as transfer_sales'),
            DB::raw('COALESCE(SUM(total), 0) as total_sales'),
            DB::raw('COALESCE(COUNT(*), 0) as total_transactions')
        )
        ->first();

    Log::info('Calculando ventas para cierre:', [
        'cash_register_id' => $cashRegister->id,
        'cash_sales' => $salesData->cash_sales ?? 0,
        'total_sales' => $salesData->total_sales ?? 0,
        'transactions' => $salesData->total_transactions ?? 0
    ]);

    // Actualizar la caja con los totales calculados
    $cashRegister->update([
        'closing_balance' => $request->closing_balance,
        'cash_sales' => floatval($salesData->cash_sales ?? 0),
        'card_sales' => floatval($salesData->card_sales ?? 0),
        'transfer_sales' => floatval($salesData->transfer_sales ?? 0),
        'total_sales' => floatval($salesData->total_sales ?? 0),
        'total_transactions' => intval($salesData->total_transactions ?? 0),
        'closed_at' => now(),
        'status' => 'closed',
        'notes' => $request->notes ? ($cashRegister->notes ? $cashRegister->notes . "\n" : '') . $request->notes : $cashRegister->notes
    ]);

    // Forzar recarga de la caja
    $cashRegister->refresh();

    return response()->json([
        'message' => 'Caja cerrada correctamente',
        'cash_register' => $cashRegister
    ]);
}

    /**
     * Reporte de caja específica
     */
    /**
 * Reporte de caja específica
 */
public function report($id)
{
    $cashRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)
        ->with('user')
        ->findOrFail($id);

    // Obtener ventas del período
    $sales = Sale::where('tenant_id', Auth::user()->tenant_id)
        ->where('cash_register_id', $id)
        ->whereBetween('created_at', [$cashRegister->opened_at, $cashRegister->closed_at ?? now()])
        ->where('status', 'paid')
        ->with('items.product')
        ->orderBy('created_at', 'desc')
        ->get();

    // Recalcular totales con COALESCE para evitar NULL
    $totalSales = Sale::where('cash_register_id', $id)
        ->where('status', 'paid')
        ->sum('total') ?? 0;

    $cashSales = Sale::where('cash_register_id', $id)
        ->where('status', 'paid')
        ->where('payment_method', 'cash')
        ->sum('total') ?? 0;

    $cardSales = Sale::where('cash_register_id', $id)
        ->where('status', 'paid')
        ->where('payment_method', 'card')
        ->sum('total') ?? 0;

    $transferSales = Sale::where('cash_register_id', $id)
        ->where('status', 'paid')
        ->where('payment_method', 'transfer')
        ->sum('total') ?? 0;

    $transactions = Sale::where('cash_register_id', $id)
        ->where('status', 'paid')
        ->count();

    $openingBalance = floatval($cashRegister->opening_balance ?? 0);
    $cashSalesTotal = floatval($cashSales ?? 0);
    $expectedBalance = $openingBalance + $cashSalesTotal;
    $closingBalance = floatval($cashRegister->closing_balance ?? 0);
    $difference = $closingBalance - $expectedBalance;

    return response()->json([
        'cash_register' => $cashRegister,
        'sales' => $sales,
        'summary' => [
            'total_sales' => floatval($totalSales ?? 0),
            'cash_sales' => $cashSalesTotal,
            'card_sales' => floatval($cardSales ?? 0),
            'transfer_sales' => floatval($transferSales ?? 0),
            'transactions' => intval($transactions ?? 0),
            'expected_balance' => $expectedBalance,
            'difference' => $difference
        ]
    ]);
}
    /**
     * Historial de cierres de caja
     */
    public function history()
    {
        $registers = CashRegister::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'closed')
            ->with('user')
            ->orderBy('closed_at', 'desc')
            ->paginate(20);

        return response()->json($registers);
    }
}
