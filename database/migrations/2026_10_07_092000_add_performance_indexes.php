<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->index(['branch_id', 'transaction_date'], 'sales_branch_date_index');
            $table->index(['status', 'transaction_date'], 'sales_status_date_index');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'bills_status_due_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['branch_id', 'booking_date'], 'bookings_branch_date_index');
            $table->index(['status', 'booking_date'], 'bookings_status_date_index');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['status', 'expense_date'], 'expenses_status_date_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('payment_date', 'payments_date_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('attendance_date', 'attendances_date_index');
        });

        Schema::table('overtime_records', function (Blueprint $table) {
            $table->index(['status', 'overtime_date'], 'overtime_status_date_index');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index('movement_date', 'inv_movements_date_index');
        });

        Schema::table('digital_accounts', function (Blueprint $table) {
            $table->index(['status', 'renewal_date'], 'digital_accounts_status_renewal_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropIndex('sales_branch_date_index');
            $table->dropIndex('sales_status_date_index');
        });

        Schema::table('bills', fn (Blueprint $table) => $table->dropIndex('bills_status_due_index'));

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_branch_date_index');
            $table->dropIndex('bookings_status_date_index');
        });

        Schema::table('expenses', fn (Blueprint $table) => $table->dropIndex('expenses_status_date_index'));

        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('payments_date_index'));

        Schema::table('attendances', fn (Blueprint $table) => $table->dropIndex('attendances_date_index'));

        Schema::table('overtime_records', fn (Blueprint $table) => $table->dropIndex('overtime_status_date_index'));

        Schema::table('inventory_movements', fn (Blueprint $table) => $table->dropIndex('inv_movements_date_index'));

        Schema::table('digital_accounts', fn (Blueprint $table) => $table->dropIndex('digital_accounts_status_renewal_index'));
    }
};
