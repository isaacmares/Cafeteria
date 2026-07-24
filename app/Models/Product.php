<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute; // Añadido para Laravel 11+

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'barcode',
        'cost',
        'price',
        'stock',
        'image',
        'is_active',
        'category_id',
        'description',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ESTO ES CLAVE: Le dice a Laravel que siempre envíe esta propiedad a Vue
    protected $appends = ['image_url'];

    // Accessor moderno (Laravel 9, 10, 11) de forma segura
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Validación estricta: Si la imagen existe y no está vacía, devuelve la URL.
                if (!empty($this->image) && trim($this->image) !== '') {
                    return asset('storage/' . $this->image);
                }

                return null;
            }
        );
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
