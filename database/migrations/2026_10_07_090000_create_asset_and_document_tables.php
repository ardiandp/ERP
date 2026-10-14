<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->foreignId('asset_category_id')->constrained();
            $table->string('name');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 20, 2)->nullable();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->string('location')->nullable();
            $table->string('condition')->default('GOOD');
            $table->string('status')->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches');
            $table->foreignId('to_branch_id')->nullable()->constrained('branches');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->date('movement_date');
            $table->string('reason')->nullable();
            $table->foreignId('moved_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('storage_disk')->default('public');
            $table->string('storage_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->dateTime('uploaded_at')->nullable();
            $table->string('checksum')->nullable();
            $table->timestamps();
        });

        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->constrained()->cascadeOnDelete();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('collection_name')->nullable();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
        });

        Schema::create('digital_account_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('digital_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('digital_account_categories');
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_accounts');
        Schema::dropIfExists('digital_account_categories');
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
