<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyTax extends Model
{
    use HasFactory;

    protected $table = 'property_taxes';

    protected $fillable = [
        'branch_id',
        'asset_id',
        'nop',
        'alamat_op',
        'desa',
        'kecamatan',
        'kota_kab',
        'nama_wp',
        'npwp',
        'tahun',
        'luas_bumi',
        'luas_bangunan',
        'pajak',
        'sppt_yg_dibayar',
        'tgl_bayar',
        'status_bayar',
        'bukti_bayar',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'luas_bumi' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
        'pajak' => 'decimal:2',
        'sppt_yg_dibayar' => 'decimal:2',
        'tgl_bayar' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->status_bayar = self::calculateStatus(
                (float) ($model->pajak ?? 0),
                (float) ($model->sppt_yg_dibayar ?? 0),
                $model->tgl_bayar ? $model->tgl_bayar->format('Y-m-d') : null
            );
        });
    }

    public static function calculateStatus(float $pajak, float $bayar, ?string $tglBayar = null): string
    {
        if ($pajak <= 0 && $bayar <= 0) {
            return ! empty($tglBayar) ? 'LUNAS' : 'BELUM_LUNAS';
        }

        if ($bayar >= $pajak && $pajak > 0) {
            return 'LUNAS';
        }

        if ($bayar > 0 && $bayar < $pajak) {
            return 'KURANG_BAYAR';
        }

        return 'BELUM_LUNAS';
    }

    public function getSisaPajakAttribute(): float
    {
        $pajak = (float) ($this->pajak ?? 0);
        $bayar = (float) ($this->sppt_yg_dibayar ?? 0);

        return max(0, $pajak - $bayar);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
