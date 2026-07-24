<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('folio')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('status', [
                'pending',
                'paid',
                'cancelled'
            ])->default('paid');

            // Sin llave foránea por ahora - solo el campo
            $table->unsignedBigInteger('cash_register_id')->nullable();

            $table->string('payment_method')->default('cash');
            $table->decimal('received', 10, 2)->nullable();
            $table->decimal('change', 10, 2)->nullable();

            $table->foreignId('user_id')->constrained();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
