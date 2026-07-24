<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SalesPageController extends Controller
{

    /**
     * Historial de ventas
     */
    public function index()
    {
        return Inertia::render('Sales/Index');
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

}
