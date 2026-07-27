<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:100',
                'barcode' => 'nullable|string|max:100',
                'cost' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'is_active' => 'nullable|boolean',
            ]);

            $imagePath = null;

            // Verificamos de forma segura si la imagen viene y es válida
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                // Guardamos generando un nombre único por seguridad
                $imagePath = $request->file('image')->storeAs(
                    'products',
                    uniqid() . '.' . $request->file('image')->extension(),
                    'public'
                );
            }

            $product = Product::create([
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'cost' => $data['cost'],
                'price' => $data['price'],
                'stock' => $data['stock'] ?? 0,
                'image' => $imagePath,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return response()->json([
                'message' => 'Producto creado exitosamente',
                'product' => $product
            ], 201);

        } catch (\Exception $e) {
            // ESTO ES LA MAGIA: Si hay un error, lo enviamos al frontend
            return response()->json([
                'message' => 'Fallo en ' . basename($e->getFile()) . ' línea ' . $e->getLine() . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Verificar que el producto pertenece al tenant del usuario
        if ($product->tenant_id !== Auth::user()->tenant_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Verificar que el producto pertenece al tenant del usuario
        if ($product->tenant_id !== Auth::user()->tenant_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'cost' => 'sometimes|numeric|min:0',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Validar archivo
            'is_active' => 'nullable|boolean',
        ]);

     // Procesar la nueva imagen si se envió una
        if ($request->hasFile('image')) {
            // 1. Validar estrictamente que el string no esté vacío
            if (!empty($product->image) && trim($product->image) !== '') {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']);
        }

        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Verificar que el producto pertenece al tenant del usuario
        if ($product->tenant_id !== Auth::user()->tenant_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Eliminar la imagen física antes de borrar el registro
       // 2. Misma validación estricta al eliminar
        if (!empty($product->image) && trim($product->image) !== '') {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ]);
    }
}
