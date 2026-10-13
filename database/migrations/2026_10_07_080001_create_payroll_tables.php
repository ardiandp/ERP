<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type');
            $table->string('calculation_method')->default('FIXED');
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_bpjs')->default(false);
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('employee_salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->date('effective_date');
            $table->decimal('basic_salary', 20, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('salary_component_id')->constrained();
            $table->decimal('amount', 20, 2)->default(0);
            $table->string('calculation_type')->default('FIXED');
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('payment_date')->nullable();
            $table->string('status')->default('OPEN');
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->decimal('basic_salary', 20, 2)->default(0);
            $table->decimal('total_earnings', 20, 2)->default(0);
            $table->decimal('total_deductions', 20, 2)->default(0);
            $table->decimal('net_salary', 20, 2)->default(0);
            $table->string('status')->default('DRAFT');
            $table->dateTime('generated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained();
            $table->foreignId('salary_component_id')->nullable()->constrained();
            $table->string('component_name');
            $table->string('type');
            $table->decimal('amount', 20, 2)->default(0);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained();
            $table->string('type');
            $table->string('description');
            $table->decimal('amount', 20, 2);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payslip_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->json('layout_config')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('template_id')->nullable()->constrained('payslip_templates');
            $table->string('payslip_number')->unique();
            $table->date('issue_date');
            $table->decimal('gross_salary', 20, 2)->default(0);
            $table->decimal('total_deduction', 20, 2)->default(0);
            $table->decimal('net_salary', 20, 2)->default(0);
            $table->string('status')->default('DRAFT');
            $table->string('pdf_path')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamps();
        });

        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained();
            $table->string('component_name');
            $table->string('type');
            $table->decimal('amount', 20, 2)->default(0);
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_items');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payslip_templates');
        Schema::dropIfExists('payroll_adjustments');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('employee_salary_structures');
        Schema::dropIfExists('salary_components');
    }
};
