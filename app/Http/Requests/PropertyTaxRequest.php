<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nop' => ['required', 'string', 'max:255'],
            'alamat_op' => ['required', 'string', 'max:255'],
            'desa' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kota_kab' => ['nullable', 'string', 'max:255'],
            'nama_wp' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:1990', 'max:2100'],
            'luas_bumi' => ['nullable', 'numeric', 'min:0'],
            'luas_bangunan' => ['nullable', 'numeric', 'min:0'],
            'pajak' => ['nullable', 'numeric', 'min:0'],
            'sppt_yg_dibayar' => ['nullable', 'numeric', 'min:0'],
            'tgl_bayar' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'bukti_bayar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nop.required' => 'Nomor Objek Pajak (NOP) wajib diisi.',
            'alamat_op.required' => 'Alamat Objek Pajak wajib diisi.',
            'nama_wp.required' => 'Nama Wajib Pajak wajib diisi.',
            'tahun.required' => 'Tahun Pajak wajib diisi.',
            'tahun.integer' => 'Tahun Pajak harus berupa 4 digit angka tahun valid.',
            'bukti_bayar.max' => 'Ukuran berkas bukti pembayaran maksimal 5 MB.',
        ];
    }
}
