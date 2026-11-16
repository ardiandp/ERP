<?php

namespace Tests\Feature;

use App\Models\BankTransaction;
use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BcaStatementImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_import_bca_individual_format_creates_and_links_transactions(): void
    {
        $csv = "No. Rekening,=,'8840430945\n";
        $csv .= "Nama,=,AKHDAN FATHUR RAHMAN\n";
        $csv .= "Mata Uang,=,IDR\n\n";
        $csv .= "Tanggal,Keterangan,Cabang,Jumlah,,Saldo\n";
        $csv .= "'01/01,TRSF E-BANKING CR 0101/FTSCY/WS95271 550000.00VERONIKA,'0497,550000.00,CR,25778778.23\n";
        $csv .= "'01/01,SWITCHING DB TRANSFER KE 014 WELLY RETNO AYU,'0998,4539000.00,DB,21239778.23\n";
        $csv .= "Saldo Awal,=,25228778.23\n";
        $csv .= "Kredit,=,550000.00\n";
        $csv .= "Debet,=,4539000.00\n";
        $csv .= "Saldo Akhir,=,21239778.23\n";

        $file = UploadedFile::fake()->createWithContent('bca-akhdan-jan.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('bank-transactions.import-bca'), [
            'csv_file' => $file,
            'year' => 2026,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify account exists
        $account = FinancialAccount::where('account_number', '8840430945')->firstOrFail();

        // Verify 2 transactions inserted for Akhdan
        $this->assertDatabaseHas('bank_transactions', [
            'financial_account_id' => $account->id,
            'transaction_type' => 'IN',
            'amount' => 550000.00,
        ]);

        $this->assertDatabaseHas('bank_transactions', [
            'financial_account_id' => $account->id,
            'transaction_type' => 'OUT',
            'amount' => 4539000.00,
        ]);

        $inTx = BankTransaction::where('financial_account_id', $account->id)->where('transaction_type', 'IN')->firstOrFail();
        $this->assertEquals('2026-01-01', $inTx->transaction_date->format('Y-m-d'));
    }

    public function test_import_bca_business_cv_format_with_period_and_comma_numbers(): void
    {
        $csv = "\"Informasi Rekening - Mutasi Rekening\",\" \",\" \",\" \",\" \",\n\n";
        $csv .= "\"No. rekening : 8831216999\"\n";
        $csv .= "\"Nama : DIAN MUSTIKA CARE CV\"\n";
        $csv .= "\"Periode : 01/01/2026 - 31/01/2026\"\n";
        $csv .= "\"Kode Mata Uang : Rp\"\n";
        $csv .= "\"Tanggal Transaksi\",\"Keterangan\",\"Cabang\",\"Jumlah\",\"Saldo\"\n";
        $csv .= "\"02/01\",\"TRSF E-BANKING CR 0201/FTSCY/WS95271 350000.00 PRICILIA MEIDY FRA\",\"7580\",\"350,000.00 CR\",\"5,992,865.35\"\n";
        $csv .= "\"02/01\",\"TRSF E-BANKING DB 0201/FTSCY/WS95051 4000000.00 top up ARDIAN\",\"8830\",\"4,000,000.00 DB\",\"1,992,865.35\"\n";
        $csv .= "\"Saldo Awal : 5,642,865.35\"\n";
        $csv .= "\"Mutasi Debet : 4,000,000.00\",\"1\"\n";
        $csv .= "\"Mutasi Kredit : 350,000.00\",\"1\"\n";
        $csv .= "\"Saldo Akhir : 1,992,865.35\"\n";

        $file = UploadedFile::fake()->createWithContent('bca-cv-jan.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('bank-transactions.import-bca'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $account = FinancialAccount::where('account_number', '8831216999')->firstOrFail();

        // Check date was constructed using period year 2026
        $this->assertDatabaseHas('bank_transactions', [
            'financial_account_id' => $account->id,
            'transaction_type' => 'IN',
            'amount' => 350000.00,
            'balance' => 5992865.35,
        ]);

        $this->assertDatabaseHas('bank_transactions', [
            'financial_account_id' => $account->id,
            'transaction_type' => 'OUT',
            'amount' => 4000000.00,
            'balance' => 1992865.35,
        ]);

        $cvTx = BankTransaction::where('financial_account_id', $account->id)->where('transaction_type', 'IN')->firstOrFail();
        $this->assertEquals('2026-01-02', $cvTx->transaction_date->format('Y-m-d'));
    }

    public function test_anti_duplicate_prevents_duplicate_bank_transactions(): void
    {
        $csv = "No. Rekening,=,'7615476862\n";
        $csv .= "Nama,=,ALIYA MADANIYA\n";
        $csv .= "Mata Uang,=,IDR\n\n";
        $csv .= "Tanggal,Keterangan,Cabang,Jumlah,,Saldo\n";
        $csv .= "'01/08/2026,TRSF E-BANKING CR MARIA,'0015,900000.00,CR,1209990.80\n";

        $file1 = UploadedFile::fake()->createWithContent('bca-alya.csv', $csv);
        $this->actingAs($this->admin)->post(route('bank-transactions.import-bca'), [
            'csv_file' => $file1,
        ]);

        $account = FinancialAccount::where('account_number', '7615476862')->firstOrFail();
        $this->assertEquals(1, $account->bankTransactions()->count());

        // Second import of the identical file
        $file2 = UploadedFile::fake()->createWithContent('bca-alya.csv', $csv);
        $response = $this->actingAs($this->admin)->post(route('bank-transactions.import-bca'), [
            'csv_file' => $file2,
        ]);

        $response->assertRedirect();
        // Still exactly 1 transaction, duplicate was skipped
        $this->assertEquals(1, $account->bankTransactions()->count());
    }

    public function test_auto_registers_new_unknown_bank_account_on_the_fly(): void
    {
        $newAccNum = '9988776655';
        $newName = 'LAYA TEST ACCOUNT';

        $csv = "No. Rekening,=,'{$newAccNum}\n";
        $csv .= "Nama,=,{$newName}\n";
        $csv .= "Mata Uang,=,IDR\n\n";
        $csv .= "Tanggal,Keterangan,Cabang,Jumlah,,Saldo\n";
        $csv .= "'10/05/2026,TRSF MASUK DP JOGLO,'0015,1500000.00,CR,1500000.00\n";

        $file = UploadedFile::fake()->createWithContent('bca-new-laya.csv', $csv);

        $this->assertDatabaseMissing('financial_accounts', ['account_number' => $newAccNum]);

        $this->actingAs($this->admin)->post(route('bank-transactions.import-bca'), [
            'csv_file' => $file,
        ]);

        // Account must be auto-created and linked
        $account = FinancialAccount::where('account_number', $newAccNum)->first();
        $this->assertNotNull($account, 'New financial account must be automatically registered.');
        $this->assertStringContainsString($newName, $account->account_name);
        $this->assertEquals(1, $account->bankTransactions()->count());
    }

    public function test_bank_transactions_page_separates_accounts_with_tabs(): void
    {
        $accAkhdan = FinancialAccount::where('account_number', '8840430945')->firstOrFail();
        $accCv = FinancialAccount::where('account_number', '8831216999')->firstOrFail();

        $accAkhdan->bankTransactions()->create([
            'transaction_date' => '2026-01-05',
            'transaction_type' => 'IN',
            'amount' => 777000,
            'description' => 'Transfer Khusus Akhdan',
            'reconciliation_status' => 'UNRECONCILED',
        ]);

        $accCv->bankTransactions()->create([
            'transaction_date' => '2026-01-05',
            'transaction_type' => 'IN',
            'amount' => 888000,
            'description' => 'Transfer Khusus CV Dian Mustika',
            'reconciliation_status' => 'UNRECONCILED',
        ]);

        // Filter for Akhdan only
        $viewAkhdan = $this->actingAs($this->admin)->get(route('bank-transactions.index', [
            'financial_account_id' => $accAkhdan->id,
        ]));
        $viewAkhdan->assertOk();
        $viewAkhdan->assertSee('Transfer Khusus Akhdan');
        $viewAkhdan->assertDontSee('Transfer Khusus CV Dian Mustika');

        // Filter for CV only
        $viewCv = $this->actingAs($this->admin)->get(route('bank-transactions.index', [
            'financial_account_id' => $accCv->id,
        ]));
        $viewCv->assertOk();
        $viewCv->assertSee('Transfer Khusus CV Dian Mustika');
        $viewCv->assertDontSee('Transfer Khusus Akhdan');
    }

    public function test_bank_transactions_export_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('bank-transactions.export'));
        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('content-type'));
    }

    public function test_manual_registration_of_new_bank_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('bank-transactions.accounts.store'), [
            'account_number' => '7615998811',
            'account_name' => 'BCA Laya Individu',
            'opening_balance' => 1500000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_accounts', [
            'account_number' => '7615998811',
            'account_name' => 'BCA Laya Individu',
            'account_type' => 'BANK',
            'opening_balance' => 1500000,
        ]);
    }

    public function test_bank_transactions_search_by_description_and_amount(): void
    {
        $account = FinancialAccount::firstOrFail();

        $account->bankTransactions()->create([
            'transaction_date' => '2026-02-10',
            'transaction_type' => 'IN',
            'amount' => 1234567,
            'description' => 'Pembayaran Paket Spa Melati',
            'reference_number' => 'REF-9988',
            'reconciliation_status' => 'UNRECONCILED',
        ]);

        // Search by description keyword
        $respDesc = $this->actingAs($this->admin)->get(route('bank-transactions.index', [
            'search' => 'Paket Spa Melati',
        ]));
        $respDesc->assertOk();
        $respDesc->assertSee('Pembayaran Paket Spa Melati');

        // Search by amount
        $respAmount = $this->actingAs($this->admin)->get(route('bank-transactions.index', [
            'search' => '1234567',
        ]));
        $respAmount->assertOk();
        $respAmount->assertSee('Pembayaran Paket Spa Melati');

        // Search by reference
        $respRef = $this->actingAs($this->admin)->get(route('bank-transactions.index', [
            'search' => 'REF-9988',
        ]));
        $respRef->assertOk();
        $respRef->assertSee('REF-9988');
    }
}
