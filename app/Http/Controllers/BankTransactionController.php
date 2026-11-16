<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\FinancialAccount;
use App\Services\BankStatementParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BankTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $selectedAccountId = $request->integer('financial_account_id') ?: null;

        $query = BankTransaction::with('financialAccount')
            ->when($selectedAccountId, fn ($q) => $q->where('financial_account_id', $selectedAccountId))
            ->when($request->filled('status'), fn ($q) => $q->where('reconciliation_status', $request->input('status')))
            ->when($request->filled('transaction_type'), fn ($q) => $q->where('transaction_type', $request->input('transaction_type')))
            ->when($request->filled('from'), fn ($q) => $q->where('transaction_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('transaction_date', '<=', $request->input('to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $raw = trim($request->input('search'));
                $term = '%'.$raw.'%';
                $cleanNum = str_replace(['.', ',', ' '], '', $raw);

                $q->where(function ($sub) use ($term, $raw, $cleanNum) {
                    $sub->where('description', 'like', $term)
                        ->orWhere('reference_number', 'like', $term)
                        ->orWhere('external_reference', 'like', $term)
                        ->orWhereHas('financialAccount', function ($accQ) use ($term) {
                            $accQ->where('account_name', 'like', $term)
                                ->orWhere('account_number', 'like', $term);
                        });

                    if (is_numeric($cleanNum) && strlen($cleanNum) >= 2) {
                        $sub->orWhere('amount', '=', (float) $cleanNum)
                            ->orWhere('amount', 'like', '%'.$cleanNum.'%')
                            ->orWhere('balance', '=', (float) $cleanNum)
                            ->orWhere('balance', 'like', '%'.$cleanNum.'%');
                    }

                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) || preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $raw)) {
                        $parsedDate = date('Y-m-d', strtotime(str_replace('/', '-', $raw)));
                        $sub->orWhereDate('transaction_date', $parsedDate);
                    }
                });
            });

        // Compute metrics for filtered query
        $totalIn = (float) (clone $query)->where('transaction_type', 'IN')->sum('amount');
        $totalOut = (float) (clone $query)->where('transaction_type', 'OUT')->sum('amount');
        $unreconciledCount = (clone $query)->where('reconciliation_status', 'UNRECONCILED')->count();

        // Latest balance for selected account
        $latestBalance = 0.0;
        if ($selectedAccountId) {
            $latestTx = BankTransaction::where('financial_account_id', $selectedAccountId)
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->first();
            $latestBalance = (float) ($latestTx?->balance ?? FinancialAccount::find($selectedAccountId)?->opening_balance ?? 0);
        }

        $transactions = $query->orderByDesc('transaction_date')->orderByDesc('id')->paginate(30)->withQueryString();

        // Bank accounts for Tab Switcher
        $accounts = FinancialAccount::withCount('bankTransactions')
            ->where('account_type', 'BANK')
            ->orWhereHas('bankTransactions')
            ->orderBy('account_name')
            ->get();

        return view('bank-transactions.index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'banks' => Bank::orderBy('name')->get(),
            'selectedAccountId' => $selectedAccountId,
            'selectedAccount' => $selectedAccountId ? FinancialAccount::find($selectedAccountId) : null,
            'metrics' => [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'net_flow' => $totalIn - $totalOut,
                'unreconciled_count' => $unreconciledCount,
                'latest_balance' => $latestBalance,
            ],
            'filters' => [
                'status' => $request->input('status'),
                'transaction_type' => $request->input('transaction_type'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('bank-transactions.create', [
            'accounts' => FinancialAccount::orderBy('account_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'financial_account_id' => ['required', 'exists:financial_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'transaction_type' => ['required', 'in:IN,OUT'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
            'balance' => ['nullable', 'numeric'],
            'external_reference' => ['nullable', 'string'],
        ]);

        BankTransaction::create(array_merge($data, ['reconciliation_status' => 'UNRECONCILED']));

        return redirect()->route('bank-transactions.index', ['financial_account_id' => $data['financial_account_id']])
            ->with('success', 'Mutasi bank berhasil dicatat.');
    }

    public function importBca(Request $request, BankStatementParserService $parser): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:15360'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'financial_account_id' => ['nullable', 'exists:financial_accounts,id'],
        ], [
            'csv_file.required' => 'Silakan pilih berkas CSV mutasi BCA.',
            'csv_file.mimes' => 'Format berkas harus berupa CSV atau TXT.',
            'csv_file.max' => 'Ukuran berkas maksimal 15 MB.',
        ]);

        $file = $request->file('csv_file');
        $defaultYear = $request->integer('year') ?: null;
        $accountId = $request->integer('financial_account_id') ?: null;

        try {
            $result = $parser->importBca($file->getRealPath(), $defaultYear, $accountId);
        } catch (\Throwable $e) {
            return back()->withErrors(['csv_file' => 'Gagal memproses berkas mutasi BCA: '.$e->getMessage()]);
        }

        $accountName = $result['account']->account_name;
        $accNum = $result['account']->account_number ?: '-';
        $formatLabel = $result['format'] === 'BCA_BUSINESS' ? 'BCA Bisnis (Badan Usaha / CV)' : 'BCA Individu';

        $msg = "Berhasil mengimpor {$result['imported_count']} mutasi untuk rekening {$accountName} ({$accNum}) via format {$formatLabel}.";
        if ($result['skipped_count'] > 0) {
            $msg .= " ({$result['skipped_count']} mutasi dilewati karena sudah ada di sistem).";
        }

        return redirect()->route('bank-transactions.index', ['financial_account_id' => $result['account']->id])
            ->with('success', $msg);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_id' => ['nullable', 'exists:banks,id'],
            'account_number' => ['required', 'string', 'max:50', 'unique:financial_accounts,account_number'],
            'account_name' => ['required', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
        ], [
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_number.unique' => 'Nomor rekening ini sudah terdaftar di sistem.',
            'account_name.required' => 'Nama akun / atas nama rekening wajib diisi.',
        ]);

        $bank = ! empty($validated['bank_id'])
            ? Bank::find($validated['bank_id'])
            : Bank::where('code', 'BCA')->first();

        $bankPrefix = $bank?->code ?? 'BANK';
        $baseCode = $bankPrefix.'-'.preg_replace('/[^0-9A-Za-z]/', '', $validated['account_number']);
        $uniqueCode = $baseCode;
        $counter = 1;
        while (FinancialAccount::where('account_code', $uniqueCode)->exists()) {
            $uniqueCode = $baseCode.'-'.$counter;
            $counter++;
        }

        $account = FinancialAccount::create([
            'account_code' => $uniqueCode,
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'account_type' => 'BANK',
            'bank_id' => $bank?->id,
            'currency' => 'IDR',
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'status' => 'ACTIVE',
        ]);

        return redirect()->route('bank-transactions.index', ['financial_account_id' => $account->id])
            ->with('success', "Rekening {$account->account_name} ({$account->account_number}) berhasil didaftarkan dan siap digunakan!");
    }

    public function export(Request $request): StreamedResponse
    {
        $selectedAccountId = $request->integer('financial_account_id') ?: null;

        $rows = BankTransaction::with('financialAccount')
            ->when($selectedAccountId, fn ($q) => $q->where('financial_account_id', $selectedAccountId))
            ->when($request->filled('status'), fn ($q) => $q->where('reconciliation_status', $request->input('status')))
            ->when($request->filled('transaction_type'), fn ($q) => $q->where('transaction_type', $request->input('transaction_type')))
            ->when($request->filled('from'), fn ($q) => $q->where('transaction_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('transaction_date', '<=', $request->input('to')))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID',
                'Tanggal',
                'Akun Rekening',
                'No. Rekening',
                'Jenis (IN/OUT)',
                'Jumlah (Rp)',
                'Saldo Akhir (Rp)',
                'Keterangan',
                'Referensi / Cabang',
                'Status Rekonsiliasi',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->transaction_date ? $row->transaction_date->format('Y-m-d') : '',
                    $row->financialAccount?->account_name,
                    $row->financialAccount?->account_number,
                    $row->transaction_type,
                    $row->amount,
                    $row->balance,
                    $row->description,
                    $row->reference_number,
                    $row->reconciliation_status,
                ]);
            }
            fclose($out);
        }, 'mutasi-bank-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
