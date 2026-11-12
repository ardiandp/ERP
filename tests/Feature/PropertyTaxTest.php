<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\PropertyTax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PropertyTaxTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_property_taxes_index_renders_and_filters(): void
    {
        $branch = Branch::firstOrFail();

        PropertyTax::create([
            'nop' => '31.71.010.001.001-0001.0',
            'alamat_op' => 'Jl. Tebet Barat No. 10',
            'desa' => 'Tebet',
            'kecamatan' => 'Tebet',
            'kota_kab' => 'Jakarta Selatan',
            'nama_wp' => 'Dian Mustika',
            'tahun' => 2026,
            'pajak' => 15000000,
            'sppt_yg_dibayar' => 15000000,
            'tgl_bayar' => '2026-05-10',
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('property-taxes.index'));
        $response->assertOk();
        $response->assertSee('31.71.010.001.001-0001.0');
        $response->assertSee('15.000.000');
        $response->assertSee('LUNAS');

        // Test filtering by tahun
        $filterResponse = $this->actingAs($this->admin)->get(route('property-taxes.index', ['tahun' => 2026]));
        $filterResponse->assertOk();
        $filterResponse->assertSee('31.71.010.001.001-0001.0');
    }

    public function test_property_tax_can_be_created_with_auto_status_calculation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('property-taxes.store'), [
            'nop' => '32.73.010.002.003-0004.0',
            'alamat_op' => 'Jl. Pajajaran No. 99',
            'desa' => 'Bantarjati',
            'kecamatan' => 'Bogor Utara',
            'kota_kab' => 'Kota Bogor',
            'nama_wp' => 'Klinik Dian Mustika Bogor',
            'npwp' => '01.234.567.8-404.000',
            'tahun' => 2026,
            'luas_bumi' => 500,
            'luas_bangunan' => 350,
            'pajak' => 10000000,
            'sppt_yg_dibayar' => 10000000,
            'tgl_bayar' => '2026-06-01',
            'keterangan' => 'Gedung Cabang Baru',
        ]);

        $response->assertRedirect(route('property-taxes.index'));

        $tax = PropertyTax::where('nop', '32.73.010.002.003-0004.0')->firstOrFail();
        $this->assertEquals('LUNAS', $tax->status_bayar);
        $this->assertEquals(0, $tax->sisa_pajak);
        $this->assertEquals(10000000, (float) $tax->pajak);
    }

    public function test_property_tax_partial_payment_sets_kurang_bayar(): void
    {
        $this->actingAs($this->admin)->post(route('property-taxes.store'), [
            'nop' => '32.73.010.002.003-0005.0',
            'alamat_op' => 'Jl. Sudirman No. 1',
            'nama_wp' => 'Wajib Pajak Tes',
            'tahun' => 2026,
            'pajak' => 10000000,
            'sppt_yg_dibayar' => 4000000,
            'tgl_bayar' => '2026-06-01',
        ]);

        $tax = PropertyTax::where('nop', '32.73.010.002.003-0005.0')->firstOrFail();
        $this->assertEquals('KURANG_BAYAR', $tax->status_bayar);
        $this->assertEquals(6000000, $tax->sisa_pajak);
    }

    public function test_property_tax_show_and_print_renders(): void
    {
        $tax = PropertyTax::create([
            'nop' => '31.71.010.001.001-0099.0',
            'alamat_op' => 'Jl. Thamrin No. 5',
            'nama_wp' => 'Dian Mustika Jakarta',
            'tahun' => 2026,
            'pajak' => 8000000,
            'sppt_yg_dibayar' => 0,
            'status_bayar' => 'BELUM_LUNAS',
        ]);

        $showResponse = $this->actingAs($this->admin)->get(route('property-taxes.show', $tax));
        $showResponse->assertOk();
        $showResponse->assertSee('31.71.010.001.001-0099.0');
        $showResponse->assertSee('BELUM LUNAS');

        $printResponse = $this->actingAs($this->admin)->get(route('property-taxes.print', $tax));
        $printResponse->assertOk();
        $printResponse->assertSee('DIAN MUSTIKA');
        $printResponse->assertSee('LEMBAR REKAPITULASI SPPT PBB');
    }

    public function test_property_tax_can_be_updated(): void
    {
        $tax = PropertyTax::create([
            'nop' => '31.71.010.001.001-0022.0',
            'alamat_op' => 'Alamat Awal',
            'nama_wp' => 'WP Awal',
            'tahun' => 2026,
            'pajak' => 5000000,
            'sppt_yg_dibayar' => 0,
        ]);

        $response = $this->actingAs($this->admin)->put(route('property-taxes.update', $tax), [
            'nop' => '31.71.010.001.001-0022.0',
            'alamat_op' => 'Alamat Diperbarui',
            'nama_wp' => 'WP Diperbarui',
            'tahun' => 2026,
            'pajak' => 5000000,
            'sppt_yg_dibayar' => 5000000,
            'tgl_bayar' => '2026-07-01',
        ]);

        $response->assertRedirect(route('property-taxes.show', $tax));

        $tax->refresh();
        $this->assertEquals('Alamat Diperbarui', $tax->alamat_op);
        $this->assertEquals('LUNAS', $tax->status_bayar);
    }

    public function test_property_tax_can_be_deleted(): void
    {
        $tax = PropertyTax::create([
            'nop' => '31.71.010.001.001-0033.0',
            'alamat_op' => 'Alamat Dihapus',
            'nama_wp' => 'WP Dihapus',
            'tahun' => 2026,
            'pajak' => 1000000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('property-taxes.destroy', $tax));
        $response->assertRedirect(route('property-taxes.index'));

        $this->assertDatabaseMissing('property_taxes', ['id' => $tax->id]);
    }

    public function test_template_and_export_csv_download(): void
    {
        $templateResponse = $this->actingAs($this->admin)->get(route('property-taxes.template'));
        $templateResponse->assertOk();
        $this->assertStringStartsWith('text/csv', $templateResponse->headers->get('content-type'));

        $exportResponse = $this->actingAs($this->admin)->get(route('property-taxes.export'));
        $exportResponse->assertOk();
        $this->assertStringStartsWith('text/csv', $exportResponse->headers->get('content-type'));
    }

    public function test_batch_csv_import(): void
    {
        $csvContent = "nop,alamat_op,desa,kecamatan,kota_kab,nama_wp,npwp,tahun,luas_bumi,luas_bangunan,pajak,sppt_yg_dibayar,tgl_bayar,keterangan\n";
        $csvContent .= "32.01.001.001.001-0001.0,Jl. Raya Ciawi No. 10,Ciawi,Ciawi,Kabupaten Bogor,Dian Mustika Resort,,2026,1000.00,450.00,8500000,8500000,2026-05-15,Import Sukses\n";
        $csvContent .= "32.01.001.001.001-0002.0,Jl. Raya Ciawi No. 12,Ciawi,Ciawi,Kabupaten Bogor,Dian Mustika Villa,,2026,800.00,300.00,6000000,0,,Belum Bayar\n";

        $file = UploadedFile::fake()->createWithContent('import-pbb-test.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('property-taxes.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('property-taxes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('property_taxes', [
            'nop' => '32.01.001.001.001-0001.0',
            'status_bayar' => 'LUNAS',
        ]);
        $this->assertDatabaseHas('property_taxes', [
            'nop' => '32.01.001.001.001-0002.0',
            'status_bayar' => 'BELUM_LUNAS',
        ]);
    }
}
