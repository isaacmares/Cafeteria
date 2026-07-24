<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSaleRequest;
use App\Services\Sales\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StoreSaleController extends Controller
{
    public function __construct(
        private SaleService $saleService
    ) {
    }

    public function __invoke(StoreSaleRequest $request): JsonResponse
    {
        try {
            Log::info('=== StoreSaleController INVOKE ===');
            Log::info('Usuario autenticado:', [
                'id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? 'NO TIENE TENANT'
            ]);
            Log::info('Datos recibidos:', $request->validated());

            // Verificar si el usuario tiene tenant
            if (!auth()->user()->tenant_id) {
                Log::error('Usuario sin tenant_id');
                return response()->json([
                    'message' => 'El usuario no tiene un tenant asignado'
                ], 400);
            }

            $sale = $this->saleService->create($request->validated());

            return response()->json([
                'message' => 'Venta creada correctamente.',
                'sale' => $sale->load(['items.product', 'payments', 'user'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Error de validación:', $e->errors());
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear venta:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
