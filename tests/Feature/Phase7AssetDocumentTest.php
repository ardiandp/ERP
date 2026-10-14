<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMovement;
use App\Models\Branch;
use App\Models\DigitalAccount;
use App\Models\DigitalAccountCategory;
use App\Models\MediaAttachment;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase7AssetDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_phase7_pages_render(): void
    {
        $this->actingAs($this->admin)->get(route('assets.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('assets.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('media.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('media.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('digital-accounts.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('digital-accounts.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('masterdata.index', 'asset-categories'))->assertOk();
        $this->actingAs($this->admin)->get(route('masterdata.index', 'digital-account-categories'))->assertOk();
    }

    public function test_asset_is_created_and_moved(): void
    {
        $category = AssetCategory::firstOrFail();
        $branch = Branch::firstOrFail();

        $this->actingAs($this->admin)->post(route('assets.store'), [
            'asset_category_id' => $category->id,
            'name' => 'Laptop Test',
            'branch_id' => $branch->id,
            'location' => 'Ruang IT',
            'condition' => 'GOOD',
            'status' => 'ACTIVE',
            'purchase_price' => 10000000,
        ])->assertRedirect();

        $asset = Asset::firstOrFail();
        $this->assertStringStartsWith('AST-', $asset->asset_code);

        $this->actingAs($this->admin)->post(route('assets.movements.store', $asset), [
            'to_branch_id' => $branch->id,
            'to_location' => 'Ruang Owner',
            'movement_date' => now()->toDateString(),
            'reason' => 'Relokasi',
        ])->assertRedirect();

        $this->assertDatabaseHas('asset_movements', [
            'asset_id' => $asset->id,
            'from_location' => 'Ruang IT',
            'to_location' => 'Ruang Owner',
        ]);
        $this->assertSame('Ruang Owner', $asset->fresh()->location);

        $movement = AssetMovement::firstOrFail();
        $this->actingAs($this->admin)->patch(route('asset-movements.approve', $movement))->assertRedirect();
        $this->assertNotNull($movement->fresh()->approved_by);

        $this->actingAs($this->admin)->get(route('assets.show', $asset))->assertOk();
    }

    public function test_media_file_uploaded_and_attached(): void
    {
        Storage::fake('public');

        $asset = Asset::create([
            'asset_code' => 'AST-TEST-01',
            'asset_category_id' => AssetCategory::firstOrFail()->id,
            'name' => 'Aset Lampiran',
            'condition' => 'GOOD',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($this->admin)->post(route('media.store'), [
            'file' => UploadedFile::fake()->create('kontrak.pdf', 100, 'application/pdf'),
            'attachable_type' => 'asset',
            'attachable_id' => $asset->id,
            'collection_name' => 'documents',
        ])->assertRedirect();

        $media = MediaFile::firstOrFail();
        $this->assertSame('kontrak.pdf', $media->original_name);
        $this->assertNotNull($media->checksum);
        Storage::disk('public')->assertExists($media->storage_path);

        $this->assertDatabaseHas('media_attachments', [
            'media_file_id' => $media->id,
            'attachable_type' => Asset::class,
            'attachable_id' => $asset->id,
        ]);
        $this->assertSame(1, MediaAttachment::count());

        $this->actingAs($this->admin)->get(route('media.show', $media))->assertOk();
    }

    public function test_digital_account_crud(): void
    {
        $category = DigitalAccountCategory::firstOrFail();

        $this->actingAs($this->admin)->post(route('digital-accounts.store'), [
            'category_id' => $category->id,
            'name' => 'Domain Utama',
            'username' => 'admin',
            'email' => 'admin@dianmustika.test',
            'url' => 'https://dianmustika.test',
            'renewal_date' => now()->addYear()->toDateString(),
            'status' => 'ACTIVE',
        ])->assertRedirect();

        $account = DigitalAccount::firstOrFail();
        $this->assertSame('Domain Utama', $account->name);

        $this->actingAs($this->admin)->put(route('digital-accounts.update', $account), [
            'category_id' => $category->id,
            'name' => 'Domain Utama Updated',
            'status' => 'ACTIVE',
        ])->assertRedirect();
        $this->assertSame('Domain Utama Updated', $account->fresh()->name);

        $this->actingAs($this->admin)->delete(route('digital-accounts.destroy', $account))->assertRedirect();
        $this->assertDatabaseMissing('digital_accounts', ['id' => $account->id]);
    }
}
