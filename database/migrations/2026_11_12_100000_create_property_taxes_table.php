<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('nop');
            $table->string('alamat_op');
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota_kab')->nullable();
            $table->string('nama_wp');
            $table->string('npwp')->nullable();
            $table->integer('tahun');
            $table->decimal('luas_bumi', 15, 2)->nullable();
            $table->decimal('luas_bangunan', 15, 2)->nullable();
            $table->decimal('pajak', 15, 2)->nullable();
            $table->decimal('sppt_yg_dibayar', 15, 2)->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->string('status_bayar', 50)->default('BELUM_LUNAS');
            $table->string('bukti_bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nop');
            $table->index('tahun');
            $table->index('status_bayar');
            $table->index('tgl_bayar');
            $table->index(['tahun', 'status_bayar']);
            $table->index(['kota_kab', 'kecamatan']);
            $table->index(['nop', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_taxes');
    }
};
