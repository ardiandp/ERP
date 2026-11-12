<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\FinancialAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BankStatementParserService
{
    /**
     * Parse and import BCA statement (auto-detects Individual vs Business/CV format).
     *
     * @return array{
     *     account: FinancialAccount,
     *     imported_count: int,
     *     skipped_count: int,
     *     total_credit: float,
     *     total_debit: float,
     *     format: string
     * }
     */
    public function importBca(string $filePath, ?int $defaultYear = null, ?int $financialAccountId = null): array
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("Berkas mutasi bank tidak ditemukan di: {$filePath}");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            throw new InvalidArgumentException('Berkas CSV kosong.');
        }

        // 1. Detect statement format and metadata
        $format = $this->detectFormat($lines);
        $metadata = $this->extractMetadata($lines, $format, $defaultYear);

        // 2. Resolve Financial Account
        $account = $this->resolveAccount($metadata['account_number'], $metadata['account_name'], $financialAccountId);

        // 3. Parse transactions
        $year = $metadata['year'] ?? $defaultYear ?? (int) date('Y');

        return DB::transaction(function () use ($lines, $format, $year, $account) {
            $importedCount = 0;
            $skippedCount = 0;
            $totalCredit = 0.0;
            $totalDebit = 0.0;

            foreach ($lines as $line) {
                $row = str_getcsv($line);
                if (empty(array_filter($row))) {
                    continue;
                }

                $parsedRow = $this->parseRow($row, $format, $year);
                if (! $parsedRow) {
                    continue; // Skip header/footer/invalid rows
                }

                $hash = 'BCA-'.md5(
                    $account->id.'|'.
                    $parsedRow['date'].'|'.
                    $parsedRow['type'].'|'.
                    $parsedRow['amount'].'|'.
                    $parsedRow['description'].'|'.
                    $parsedRow['balance']
                );

                // Anti-duplication check
                $exists = BankTransaction::where('financial_account_id', $account->id)
                    ->where(function ($q) use ($hash, $parsedRow) {
                        $q->where('external_reference', $hash)
                            ->orWhere(function ($sub) use ($parsedRow) {
                                $sub->where('transaction_date', $parsedRow['date'])
                                    ->where('amount', $parsedRow['amount'])
                                    ->where('transaction_type', $parsedRow['type'])
                                    ->where('description', $parsedRow['description'])
                                    ->where('balance', $parsedRow['balance']);
                            });
                    })
                    ->exists();

                if ($exists) {
                    $skippedCount++;

                    continue;
                }

                BankTransaction::create([
                    'financial_account_id' => $account->id,
                    'transaction_date' => $parsedRow['date'],
                    'transaction_type' => $parsedRow['type'],
                    'amount' => $parsedRow['amount'],
                    'description' => $parsedRow['description'],
                    'reference_number' => $parsedRow['reference_number'],
                    'balance' => $parsedRow['balance'],
                    'external_reference' => $hash,
                    'reconciliation_status' => 'UNRECONCILED',
                    'imported_at' => now(),
                ]);

                if ($parsedRow['type'] === 'IN') {
                    $totalCredit += $parsedRow['amount'];
                } else {
                    $totalDebit += $parsedRow['amount'];
                }

                $importedCount++;
            }

            return [
                'account' => $account,
                'imported_count' => $importedCount,
                'skipped_count' => $skippedCount,
                'total_credit' => $totalCredit,
                'total_debit' => $totalDebit,
                'format' => $format,
            ];
        });
    }

    protected function detectFormat(array $lines): string
    {
        $preview = implode("\n", array_slice($lines, 0, 10));

        if (stripos($preview, 'Informasi Rekening') !== false || stripos($preview, 'Mutasi Rekening') !== false) {
            return 'BCA_BUSINESS';
        }

        if (stripos($preview, 'No. Rekening,=') !== false || stripos($preview, 'Mata Uang,=') !== false) {
            return 'BCA_INDIVIDUAL';
        }

        // Fallback: check columns
        foreach ($lines as $line) {
            if (stripos($line, 'Tanggal,Keterangan,Cabang') !== false) {
                return 'BCA_INDIVIDUAL';
            }
            if (stripos($line, 'Tanggal Transaksi') !== false) {
                return 'BCA_BUSINESS';
            }
        }

        return 'BCA_INDIVIDUAL';
    }

    protected function extractMetadata(array $lines, string $format, ?int $defaultYear): array
    {
        $accountNumber = null;
        $accountName = null;
        $year = $defaultYear;

        foreach ($lines as $line) {
            $cleanLine = trim($line);

            if ($format === 'BCA_BUSINESS') {
                // "No. rekening : 8831216999"
                if (preg_match('/no\.?\s*rekening\s*:\s*([0-9]+)/i', $cleanLine, $matches)) {
                    $accountNumber = trim($matches[1]);
                }
                // "Nama : DIAN MUSTIKA CARE CV"
                if (preg_match('/nama\s*:\s*([^",]+)/i', $cleanLine, $matches)) {
                    $accountName = trim($matches[1]);
                }
                // "Periode : 01/01/2026 - 31/01/2026"
                if (preg_match('/periode\s*:\s*.*?\b(20[2-9][0-9])\b/i', $cleanLine, $matches)) {
                    $year = (int) $matches[1];
                }
            } else {
                // No. Rekening,=,'8840430945 or '7615476862
                if (preg_match('/no\.?\s*rekening\s*,=\s*,[\'"]?([0-9]+)/i', $cleanLine, $matches)) {
                    $accountNumber = trim($matches[1]);
                }
                // Nama,=,AKHDAN FATHUR RAHMAN
                if (preg_match('/nama\s*,=\s*,[\'"]?([^",\r\n]+)/i', $cleanLine, $matches)) {
                    $accountName = trim($matches[1]);
                }
            }
        }

        return [
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'year' => $year,
        ];
    }

    protected function resolveAccount(?string $accountNumber, ?string $accountName, ?int $financialAccountId): FinancialAccount
    {
        if ($financialAccountId) {
            $account = FinancialAccount::find($financialAccountId);
            if ($account) {
                return $account;
            }
        }

        if ($accountNumber) {
            $account = FinancialAccount::where('account_number', $accountNumber)->first();
            if ($account) {
                return $account;
            }
        }

        // Try matching by account_name if available
        if ($accountName) {
            $account = FinancialAccount::where('account_name', 'like', "%{$accountName}%")->first();
            if ($account) {
                if ($accountNumber && empty($account->account_number)) {
                    $account->update(['account_number' => $accountNumber]);
                }

                return $account;
            }
        }

        $bca = Bank::where('code', 'BCA')->first();

        // Auto-create new FinancialAccount if not existing
        $cleanAccNum = $accountNumber ?: 'BCA-'.date('YmdHis');
        $cleanName = $accountName ? "BCA {$accountName}" : "BCA {$cleanAccNum}";

        $baseCode = $accountNumber ? 'BCA-'.$accountNumber : 'BCA-'.substr($cleanAccNum, -6);
        $code = $baseCode;
        $counter = 1;
        while (FinancialAccount::where('account_code', $code)->exists()) {
            $code = $baseCode.'-'.$counter;
            $counter++;
        }

        return FinancialAccount::create([
            'account_code' => $code,
            'account_name' => $cleanName,
            'account_number' => $accountNumber,
            'account_type' => 'BANK',
            'bank_id' => $bca?->id,
            'currency' => 'IDR',
            'opening_balance' => 0,
            'status' => 'ACTIVE',
        ]);
    }

    protected function parseRow(array $row, string $format, int $year): ?array
    {
        $firstCell = trim(str_replace("'", '', $row[0] ?? ''));

        // Check if header, empty, or footer
        if (
            empty($firstCell) ||
            stripos($firstCell, 'Tanggal') !== false ||
            stripos($firstCell, 'Informasi') !== false ||
            stripos($firstCell, 'No. Rekening') !== false ||
            stripos($firstCell, 'Nama') !== false ||
            stripos($firstCell, 'Mata Uang') !== false ||
            stripos($firstCell, 'Periode') !== false ||
            stripos($firstCell, 'Saldo Awal') !== false ||
            stripos($firstCell, 'Saldo Akhir') !== false ||
            stripos($firstCell, 'Mutasi') !== false ||
            stripos($firstCell, 'Kredit') !== false ||
            stripos($firstCell, 'Debet') !== false
        ) {
            return null;
        }

        // Parse Date: format DD/MM or DD/MM/YYYY
        $date = $this->parseDate($firstCell, $year);
        if (! $date) {
            return null;
        }

        $description = trim($row[1] ?? '');
        $branchCode = trim(str_replace("'", '', $row[2] ?? ''));

        if ($format === 'BCA_BUSINESS') {
            // Business format:
            // Col 3: "350,000.00 CR" or "4,000,000.00 DB"
            // Col 4: "5,992,865.35" (Saldo)
            $rawAmountType = trim($row[3] ?? '');
            if (! preg_match('/([\d\.,]+)\s*(CR|DB)/i', $rawAmountType, $matches)) {
                return null;
            }

            $amount = (float) str_replace([',', ' '], '', $matches[1]);
            $type = strtoupper($matches[2]) === 'CR' ? 'IN' : 'OUT';
            $balance = isset($row[4]) ? (float) str_replace([',', ' '], '', $row[4]) : 0.0;
        } else {
            // Individual format:
            // Col 3: 550000.00
            // Col 4: CR or DB
            // Col 5: 25778778.23
            $rawAmount = trim($row[3] ?? '');
            $rawType = strtoupper(trim($row[4] ?? ''));

            if (! is_numeric(str_replace([',', ' '], '', $rawAmount)) || ! in_array($rawType, ['CR', 'DB'])) {
                return null;
            }

            $amount = (float) str_replace([',', ' '], '', $rawAmount);
            $type = $rawType === 'CR' ? 'IN' : 'OUT';
            $balance = isset($row[5]) ? (float) str_replace([',', ' '], '', $row[5]) : 0.0;
        }

        // Try to extract internal reference number if present in description
        $referenceNumber = $branchCode ?: null;
        if (preg_match('#\b(\d{4}/[A-Z0-9]+/[A-Z0-9]+)\b#', $description, $m)) {
            $referenceNumber = $m[1];
        }

        return [
            'date' => $date,
            'description' => $description,
            'reference_number' => $referenceNumber,
            'amount' => $amount,
            'type' => $type,
            'balance' => $balance,
        ];
    }

    protected function parseDate(string $rawDate, int $defaultYear): ?string
    {
        $rawDate = trim(str_replace("'", '', $rawDate));

        // Format DD/MM/YYYY
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $rawDate, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // Format DD/MM
        if (preg_match('#^(\d{1,2})/(\d{1,2})$#', $rawDate, $m)) {
            return sprintf('%04d-%02d-%02d', $defaultYear, (int) $m[2], (int) $m[1]);
        }

        try {
            return Carbon::parse($rawDate)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
