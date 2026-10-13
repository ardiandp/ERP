<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('nik')->nullable();
            $table->string('full_name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('employment_status')->default('ACTIVE');
            $table->date('join_date')->nullable();
            $table->date('resign_date')->nullable();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('department_id')->nullable()->constrained();
            $table->foreignId('position_id')->nullable()->constrained();
            $table->foreignId('manager_id')->nullable()->constrained('employees');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship');
            $table->string('nik')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_dependent')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('insurance_type');
            $table->string('provider');
            $table->string('membership_number');
            $table->string('insurance_class')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('employee_contribution', 20, 2)->default(0);
            $table->decimal('company_contribution', 20, 2)->default(0);
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_insurances');
        Schema::dropIfExists('employee_family_members');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
