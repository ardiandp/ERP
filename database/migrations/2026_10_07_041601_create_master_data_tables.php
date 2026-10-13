<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('customer_type')->default('REGULAR');
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('decimal_scale')->default(0);
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->foreignId('category_id')->constrained('product_categories');
            $table->foreignId('brand_id')->nullable()->constrained();
            $table->foreignId('unit_id')->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 20, 2)->default(0);
            $table->decimal('default_sell_price', 20, 2)->default(0);
            $table->decimal('minimum_stock', 20, 4)->default(0);
            $table->decimal('maximum_stock', 20, 4)->nullable();
            $table->decimal('weight', 20, 4)->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('price_type');
            $table->decimal('price', 20, 2);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('service_categories');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique();
            $table->foreignId('category_id')->constrained('service_categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('pricing_method')->default('FIXED');
            $table->integer('default_duration_minutes')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('min_weight', 20, 4)->nullable();
            $table->decimal('max_weight', 20, 4)->nullable();
            $table->decimal('price', 20, 2);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 20, 2)->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->integer('payment_terms_days')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('supplier_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained();
            $table->string('account_number');
            $table->string('account_name');
            $table->string('branch_name')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_type');
            $table->foreignId('bank_id')->nullable()->constrained();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->string('account_number')->nullable();
            $table->string('account_name');
            $table->string('currency', 3)->default('IDR');
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('method_type');
            $table->foreignId('financial_account_id')->nullable()->constrained();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('financial_accounts');
        Schema::dropIfExists('supplier_bank_accounts');
        Schema::dropIfExists('banks');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('service_package_items');
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('service_prices');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('customers');
    }
};
