<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProductPageController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index');
    }

    public function create()
    {
        return Inertia::render('Products/Create');
    }

    public function edit($id)
    {
        $product = Product::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($id);

        return Inertia::render('Products/Edit', [
            'product' => $product
        ]);
    }
}
