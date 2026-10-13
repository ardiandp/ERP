<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->date('booking_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status')->default('BOOKED');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained();
            $table->foreignId('package_id')->nullable()->constrained('service_packages');
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('scheduled_end')->nullable();
            $table->string('status')->default('BOOKED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->foreignId('cashier_id')->nullable()->constrained('users');
            $table->date('transaction_date');
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('tax', 20, 2)->default(0);
            $table->decimal('grand_total', 20, 2)->default(0);
            $table->string('status')->default('DRAFT');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('sales_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('item_type');
            $table->foreignId('product_id')->nullable()->constrained();
            $table->foreignId('service_id')->nullable()->constrained();
            $table->foreignId('package_id')->nullable()->constrained('service_packages');
            $table->string('item_name');
            $table->decimal('quantity', 20, 4)->default(1);
            $table->decimal('unit_price', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->foreignId('source_booking_item_id')->nullable()->constrained('booking_items');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('sales_transaction_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->date('return_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('DRAFT');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_transaction_item_id')->constrained();
            $table->foreignId('product_id')->nullable()->constrained();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('amount', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->string('transaction_type');
            $table->unsignedBigInteger('transaction_id');
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('financial_account_id')->nullable()->constrained();
            $table->decimal('amount', 20, 2);
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->string('status')->default('POSTED');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['transaction_type', 'transaction_id']);
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->foreignId('service_id')->nullable()->constrained();
            $table->string('commission_type');
            $table->decimal('commission_value', 20, 2);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('employee_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('sales_transaction_id')->constrained();
            $table->foreignId('sales_transaction_item_id')->nullable()->constrained();
            $table->decimal('commission_amount', 20, 2)->default(0);
            $table->string('status')->default('PENDING');
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_commissions');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('sales_transaction_items');
        Schema::dropIfExists('sales_transactions');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
    }
};
