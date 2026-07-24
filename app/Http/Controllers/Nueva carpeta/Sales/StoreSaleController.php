<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSaleRequest;
use App\Services\Sales\SaleService;
use Illuminate\Http\JsonResponse;

class StoreSaleController extends Controller
{

    public function __construct(
        private SaleService $saleService
    ) {
    }


    /**
     * Crear una venta.
     */
    public function __invoke(
        StoreSaleRequest $request
    ): JsonResponse {


        $sale = $this->saleService->create(
            $request->validated()
        );


        return response()->json([

            'message' => 'Venta creada correctamente.',

            'sale' => $sale,

        ], 201);

    }

}
