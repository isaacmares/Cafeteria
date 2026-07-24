<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Sale;

class SalesPageController extends Controller
{

    /**
     * Historial de ventas
     */
   public function index()
{

    $sales = \App\Models\Sale::query()

        ->where(
            'tenant_id',
            Auth::user()->tenant_id
        )

        ->with([
            'items',
            'payments',
            'user'
        ])

        ->latest()

        ->get();



    return Inertia::render('Sales/Index',[

        'sales'=>$sales,

        'tenant'=>Auth::user()->tenant

    ]);

}



    /**
     * Punto de venta
     */
    public function pos()
    {
        $products = Product::query()
            ->where(
                'tenant_id',
                Auth::user()->tenant_id
            )
            ->where(
                'is_active',
                true
            )
            ->where(
                'stock',
                '>',
                0
            )
            ->orderBy(
                'name'
            )
            ->get();

        return Inertia::render('Sales/POS', [

            'products' => $products,

            'tenant' => Auth::user()->tenant,

        ]);

    }


    public function ticket($id)
    {
        $sale = Sale::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->with([
                'items.product',  // Carga los productos de cada item
                'payments',       // Carga los pagos
                'user'            // Carga el usuario que hizo la venta
            ])
            ->findOrFail($id);

        return Inertia::render('Sales/Ticket', [
            'sale' => $sale,
            'tenant' => Auth::user()->tenant
        ]);
    }
}
