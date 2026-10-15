<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Booking;
use App\Models\DigitalAccount;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\PayrollPeriod;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\BillAlert;
use App\Notifications\BookingReminderAlert;
use App\Notifications\DocumentExpiryAlert;
use App\Notifications\LowStockAlert;
use App\Notifications\PayrollReadyAlert;
use App\Notifications\PendingApprovalAlert;
use Illuminate\Notifications\Notification;

class AlertService
{
    public function scanAll(): array
    {
        return [
            'low_stock' => $this->scanLowStock(),
            'bills' => $this->scanBills(),
            'pending_approval' => $this->scanPendingApprovals(),
            'booking_reminder' => $this->scanBookingReminders(),
            'payroll_ready' => $this->scanPayrollReady(),
            'document_expiry' => $this->scanDocumentExpiry(),
        ];
    }

    public function scanLowStock(): int
    {
        $count = InventoryStock::query()
            ->join('products', 'products.id', '=', 'inventory_stocks.product_id')
            ->whereColumn('inventory_stocks.quantity', '<=', 'products.minimum_stock')
            ->count();

        if ($count === 0) {
            return 0;
        }

        return $this->dispatch(new LowStockAlert($count), 'low_stock');
    }

    public function scanBills(): int
    {
        $today = now()->toDateString();
        $soon = now()->addDays(7)->toDateString();
        $sent = 0;

        $bills = Bill::where('status', 'UNPAID')
            ->whereDate('due_date', '<=', $soon)
            ->get();

        foreach ($bills as $bill) {
            $state = $bill->due_date->toDateString() < $today ? 'OVERDUE' : 'UPCOMING';

            $sent += $this->dispatch(
                new BillAlert($state, $bill->bill_number, $bill->due_date->toDateString(), $bill->id),
                'bill_'.strtolower($state).'_'.$bill->bill_number,
            );
        }

        return $sent;
    }

    public function scanPendingApprovals(): int
    {
        $count = Expense::where('status', 'DRAFT')->count()
            + PurchaseOrder::where('status', 'DRAFT')->count();

        if ($count === 0) {
            return 0;
        }

        return $this->dispatch(new PendingApprovalAlert($count), 'pending_approval');
    }

    public function scanBookingReminders(): int
    {
        $count = Booking::whereDate('booking_date', now()->toDateString())
            ->where('status', 'BOOKED')
            ->count();

        if ($count === 0) {
            return 0;
        }

        return $this->dispatch(new BookingReminderAlert($count), 'booking_reminder');
    }

    public function scanPayrollReady(): int
    {
        $sent = 0;

        foreach (PayrollPeriod::where('status', 'OPEN')->get() as $period) {
            $sent += $this->dispatch(
                new PayrollReadyAlert($period->name, $period->id),
                'payroll_ready_'.$period->id,
            );
        }

        return $sent;
    }

    public function scanDocumentExpiry(): int
    {
        $soon = now()->addDays(30)->toDateString();
        $sent = 0;

        $accounts = DigitalAccount::where('status', 'ACTIVE')
            ->whereNotNull('renewal_date')
            ->whereDate('renewal_date', '<=', $soon)
            ->get();

        foreach ($accounts as $account) {
            $sent += $this->dispatch(
                new DocumentExpiryAlert($account->name, $account->renewal_date->toDateString(), $account->id),
                'document_expiry_'.$account->id,
            );
        }

        return $sent;
    }

    protected function dispatch(Notification $notification, string $key): int
    {
        $sent = 0;

        foreach ($this->recipients() as $user) {
            $exists = $user->notifications()
                ->where('type', $notification::class)
                ->where('data->key', $key)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if (! $exists) {
                $user->notify($notification);
                $sent++;
            }
        }

        return $sent;
    }

    protected function recipients()
    {
        return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'super_admin']))->get();
    }
}
