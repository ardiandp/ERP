<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillTemplate;
use App\Support\DocumentNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BillService
{
    public function generate(array $data): array
    {
        $periodStart = Carbon::parse($data['period_start']);
        $periodEnd = Carbon::parse($data['period_end']);
        $dueDate = Carbon::parse($data['due_date']);

        if ($periodEnd->lessThan($periodStart)) {
            throw new InvalidArgumentException('Periode akhir harus setelah periode mulai.');
        }

        $templates = BillTemplate::query()
            ->where('status', 'ACTIVE')
            ->when(! empty($data['template_ids']), fn ($query) => $query->whereIn('id', $data['template_ids']))
            ->get();

        if ($templates->isEmpty()) {
            throw new InvalidArgumentException('Tidak ada template tagihan aktif.');
        }

        return DB::transaction(function () use ($templates, $periodStart, $periodEnd, $dueDate, $data) {
            $created = [];

            foreach ($templates as $template) {
                $exists = Bill::query()
                    ->where('bill_template_id', $template->id)
                    ->where('period_start', $periodStart->toDateString())
                    ->where('period_end', $periodEnd->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                $amount = (float) ($data['amounts'][$template->id] ?? $template->default_amount ?? 0);

                $created[] = Bill::create([
                    'bill_template_id' => $template->id,
                    'bill_number' => DocumentNumber::generate('BILL', Bill::class, 'bill_number', now()->toDateString()),
                    'branch_id' => $template->branch_id,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'issue_date' => now()->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'amount' => $amount,
                    'penalty_amount' => 0,
                    'total_amount' => $amount,
                    'status' => 'UNPAID',
                ]);
            }

            return $created;
        });
    }

    public function markPaid(Bill $bill): Bill
    {
        if ($bill->status === 'PAID') {
            throw new InvalidArgumentException('Tagihan sudah dibayar.');
        }

        $bill->update(['status' => 'PAID', 'paid_at' => now()]);

        return $bill;
    }
}
