<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_account_id')->constrained();
            $table->date('transaction_date');
            $table->string('transaction_type');
            $table->decimal('amount', 20, 2);
            $table->string('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('balance', 20, 2)->nullable();
            $table->string('external_reference')->nullable();
            $table->string('reconciliation_status')->default('UNRECONCILED');
            $table->dateTime('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transaction_id')->constrained();
            $table->foreignId('payment_id')->nullable()->constrained();
            $table->decimal('matched_amount', 20, 2);
            $table->string('status')->default('MATCHED');
            $table->foreignId('reconciled_by')->nullable()->constrained('users');
            $table->dateTime('reconciled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('frequency');
            $table->decimal('default_amount', 20, 2)->nullable();
            $table->integer('default_due_day')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->foreignId('financial_account_id')->nullable()->constrained();
            $table->string('status')->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_template_id')->nullable()->constrained();
            $table->string('bill_number')->unique();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('issue_date')->nullable();
            $table->date('due_date');
            $table->decimal('amount', 20, 2)->default(0);
            $table->decimal('penalty_amount', 20, 2)->default(0);
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->string('status')->default('UNPAID');
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('expense_categories');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->foreignId('expense_category_id')->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->decimal('amount', 20, 2);
            $table->date('expense_date');
            $table->foreignId('financial_account_id')->nullable()->constrained();
            $table->foreignId('bill_id')->nullable()->constrained();
            $table->text('description')->nullable();
            $table->string('status')->default('DRAFT');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('bill_templates');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_transactions');
    }
};
