<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('warehouse_type')->default('MAIN');
            $table->text('address')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('reserved_quantity', 20, 4)->default(0);
            $table->timestamps();
            $table->unique(['warehouse_id', 'product_id']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->string('movement_type');
            $table->decimal('quantity', 20, 4);
            $table->decimal('unit_cost', 20, 2)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('movement_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('warehouses');
    }
};
