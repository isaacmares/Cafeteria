<?php

declare(strict_types=1);

namespace App\Services\Sales;

use App\Models\CashRegister;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Crear una venta.
     */
    public function create(array $data): Sale
    {
        Log::info('=== INICIO SALE SERVICE ===');
        Log::info('Datos recibidos:', $data);

        return DB::transaction(function () use ($data) {

            $tenantId = Auth::user()->tenant_id;
            $userId = Auth::id();

            Log::info('Usuario autenticado:', [
                'user_id' => $userId,
                'tenant_id' => $tenantId
            ]);

            // BUSCAR CAJA - CONSULTA DIRECTA
            Log::info('Buscando caja abierta con tenant_id: ' . $tenantId);

            $cashRegister = CashRegister::where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->first();

            Log::info('Resultado búsqueda por tenant:', [
                'encontrada' => $cashRegister ? 'SI' : 'NO',
                'data' => $cashRegister ? $cashRegister->toArray() : null
            ]);

            // Si no encuentra, buscar TODAS las cajas abiertas (para debug)
            if (!$cashRegister) {
                $allOpen = CashRegister::where('status', 'open')->get();
                Log::info('TODAS las cajas abiertas en el sistema:', [
                    'cantidad' => $allOpen->count(),
                    'datos' => $allOpen->toArray()
                ]);
            }

            // Si aún no hay caja, lanzar error con mensaje detallado
            if (!$cashRegister) {
                $errorMsg = "No hay una caja abierta para el tenant {$tenantId}. ";
                $errorMsg .= "Asegúrate de abrir la caja primero.";

                Log::error($errorMsg);

                throw ValidationException::withMessages([
                    'cash_register' => $errorMsg
                ]);
            }

            Log::info('Caja encontrada correctamente:', [
                'id' => $cashRegister->id,
                'tenant_id' => $cashRegister->tenant_id,
                'status' => $cashRegister->status
            ]);

            // ... resto del código (productos, validaciones, etc)
            $products = $this->getProducts($data['items']);
            $this->validateStock($products, $data['items']);
            $totals = $this->calculateTotals($products, $data['items']);

            // En SaleService.php, al crear la venta
            $sale = Sale::create([
                'tenant_id' => $tenantId,
                'folio' => $this->generateFolio(),
                'subtotal' => $totals['subtotal'],
                'discount' => 0,
                'tax' => 0,
                'total' => $totals['total'],
                'status' => 'paid',  // ← Asegúrate que sea 'paid'
                'user_id' => Auth::id(),
                'cash_register_id' => $cashRegister->id,
                'payment_method' => $data['payment']['method'],
                'received' => $data['payment']['received'] ?? $totals['total'],
                'change' => ($data['payment']['received'] ?? $totals['total']) - $totals['total'],
            ]);

            $this->createItems($sale, $products, $data['items']);
            $this->decreaseStock($products, $data['items']);
            $this->createPayment($sale, $data, $totals);
            $this->updateCashRegister($cashRegister, $sale);

            Log::info('Venta creada:', ['sale_id' => $sale->id]);

            return $sale->load([
                'items.product',
                'payments',
                'user',
            ]);
        });
    }


    /**
     * Crear venta sin caja (modo legacy)
     */
    private function createSaleWithoutCashRegister(array $data, int $tenantId): Sale
    {
        $products = $this->getProducts($data['items']);
        $totals = $this->calculateTotals($products, $data['items']);

        return Sale::create([
            'tenant_id' => $tenantId,
            'folio' => $this->generateFolio(),
            'subtotal' => $totals['subtotal'],
            'discount' => 0,
            'tax' => 0,
            'total' => $totals['total'],
            'status' => 'paid',
            'user_id' => Auth::id(),
            'cash_register_id' => null,
            'payment_method' => $data['payment']['method'],
            'received' => $data['payment']['received'] ?? $totals['total'],
            'change' => ($data['payment']['received'] ?? $totals['total']) - $totals['total'],
        ]);
    }

    /**
     * Obtener productos pertenecientes al tenant.
     */
    private function getProducts(array $items)
    {
        $ids = collect($items)
            ->pluck('product_id')
            ->toArray();

        return Product::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    /**
     * Validar existencia y stock.
     */
    private function validateStock($products, array $items): void
    {
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                throw ValidationException::withMessages([
                    'items' => "El producto {$item['product_id']} no existe."
                ]);
            }

            if (!$product->is_active) {
                throw ValidationException::withMessages([
                    'items' => "{$product->name} está inactivo."
                ]);
            }

            if ($product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para {$product->name}."
                ]);
            }
        }
    }

    /**
     * Calcular totales.
     */
    private function calculateTotals($products, array $items): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $subtotal += ($product->price * $item['quantity']);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => 0,
            'tax' => 0,
            'total' => round($subtotal, 2),
        ];
    }

    /**
     * Generar el siguiente folio.
     */
    private function generateFolio(): string
    {
        $lastSale = Sale::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->latest('id')
            ->first();

        if (!$lastSale) {
            return 'V000001';
        }

        $number = (int) substr($lastSale->folio, 1);
        return 'V' . str_pad((string) ($number + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crear los detalles de la venta.
     */
    private function createItems(Sale $sale, $products, array $items): void
    {
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'subtotal' => round($product->price * $item['quantity'], 2),
            ]);
        }
    }

    /**
     * Descontar inventario.
     */
    private function decreaseStock($products, array $items): void
    {
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $product->decrement('stock', $item['quantity']);
        }
    }

    /**
     * Registrar el pago.
     */
    private function createPayment(Sale $sale, array $data, array $totals): void
    {
        Payment::create([
            'sale_id' => $sale->id,
            'method' => $data['payment']['method'],
            'amount' => $sale->total,
            'received' => $data['payment']['received'] ?? $sale->total,
            'change' => ($data['payment']['received'] ?? $sale->total) - $sale->total,
            'reference' => $data['payment']['reference'] ?? null
        ]);
    }


private function updateCashRegister(CashRegister $cashRegister, Sale $sale): void
{
    // Incrementar contador de transacciones
    $cashRegister->total_transactions += 1;

    // Sumar al total de ventas
    $cashRegister->total_sales += floatval($sale->total);

    // Sumar según método de pago
    switch ($sale->payment_method) {
        case 'cash':
            $cashRegister->cash_sales += floatval($sale->total);
            break;
        case 'card':
            $cashRegister->card_sales += floatval($sale->total);
            break;
        case 'transfer':
            $cashRegister->transfer_sales += floatval($sale->total);
            break;
    }

    $cashRegister->save();

    // Log para depuración
    \Log::info('Caja actualizada:', [
        'cash_register_id' => $cashRegister->id,
        'total_sales' => $cashRegister->total_sales,
        'cash_sales' => $cashRegister->cash_sales,
        'transactions' => $cashRegister->total_transactions
    ]);
}
}
