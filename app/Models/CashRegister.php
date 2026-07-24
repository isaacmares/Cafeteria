<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'opening_balance',
        'closing_balance',
        'cash_sales',
        'card_sales',
        'transfer_sales',
        'total_sales',
        'total_transactions',
        'opened_at',
        'closed_at',
        'status',
        'notes'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'transfer_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación con las ventas
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
