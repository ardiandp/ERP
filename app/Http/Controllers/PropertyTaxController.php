<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyTaxRequest;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\PropertyTax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyTaxController extends Controller
{
    public function index(Request $request): View
    {
        $query = PropertyTax::with(['branch', 'asset'])
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', $request->integer('tahun')))
            ->when($request->filled('status_bayar'), fn ($q) => $q->where('status_bayar', $request->input('status_bayar')))
            ->when($request->filled('kota_kab'), fn ($q) => $q->where('kota_kab', $request->input('kota_kab')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nop', 'like', $term)
                        ->orWhere('nama_wp', 'like', $term)
                        ->orWhere('alamat_op', 'like', $term)
                        ->orWhere('npwp', 'like', $term)
                        ->orWhere('desa', 'like', $term)
                        ->orWhere('kecamatan', 'like', $term);
                });
            });

        // Compute summary metrics on the filtered dataset
        $summaryQuery = clone $query;
        $totalCount = $summaryQuery->count();
        $totalPajak = (float) (clone $query)->sum('pajak');
        $totalBayar = (float) (clone $query)->sum('sppt_yg_dibayar');
        $totalSisa = max(0, $totalPajak - $totalBayar);
        $totalLunas = (clone $query)->where('status_bayar', 'LUNAS')->count();
        $totalBelumLunas = (clone $query)->whereIn('status_bayar', ['BELUM_LUNAS', 'KURANG_BAYAR'])->count();

        $propertyTaxes = $query->orderByDesc('tahun')->orderBy('nop')->paginate(25)->withQueryString();

        $years = PropertyTax::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');
        $cities = PropertyTax::whereNotNull('kota_kab')->where('kota_kab', '!=', '')->select('kota_kab')->distinct()->orderBy('kota_kab')->pluck('kota_kab');
        $branches = Branch::orderBy('name')->get();

        return view('property-taxes.index', [
            'propertyTaxes' => $propertyTaxes,
            'years' => $years,
            'cities' => $cities,
            'branches' => $branches,
            'metrics' => [
                'total_count' => $totalCount,
                'total_pajak' => $totalPajak,
                'total_bayar' => $totalBayar,
                'total_sisa' => $totalSisa,
                'total_lunas' => $totalLunas,
                'total_belum_lunas' => $totalBelumLunas,
            ],
            'filters' => [
                'tahun' => $request->input('tahun'),
                'status_bayar' => $request->input('status_bayar'),
                'kota_kab' => $request->input('kota_kab'),
                'branch_id' => $request->input('branch_id'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('property-taxes.create', [
            'branches' => Branch::orderBy('name')->get(),
            'assets' => Asset::orderBy('name')->get(),
        ]);
    }

    public function store(PropertyTaxRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('bukti_bayar')) {
            $data['bukti_bayar'] = $request->file('bukti_bayar')->store('property-taxes', 'public');
        }

        $propertyTax = PropertyTax::create($data);

        return redirect()->route('property-taxes.index')
            ->with('success', "Data PBB NOP {$propertyTax->nop} Tahun {$propertyTax->tahun} berhasil disimpan.");
    }

    public function show(PropertyTax $property_tax): View
    {
        $property_tax->load(['branch', 'asset', 'creator']);

        return view('property-taxes.show', [
            'tax' => $property_tax,
        ]);
    }

    public function edit(PropertyTax $property_tax): View
    {
        return view('property-taxes.edit', [
            'tax' => $property_tax,
            'branches' => Branch::orderBy('name')->get(),
            'assets' => Asset::orderBy('name')->get(),
        ]);
    }

    public function update(PropertyTaxRequest $request, PropertyTax $property_tax): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('bukti_bayar')) {
            if ($property_tax->bukti_bayar && Storage::disk('public')->exists($property_tax->bukti_bayar)) {
                Storage::disk('public')->delete($property_tax->bukti_bayar);
            }
            $data['bukti_bayar'] = $request->file('bukti_bayar')->store('property-taxes', 'public');
        }

        $property_tax->update($data);

        return redirect()->route('property-taxes.show', $property_tax)
            ->with('success', "Data PBB NOP {$property_tax->nop} berhasil diperbarui.");
    }

    public function destroy(PropertyTax $property_tax): RedirectResponse
    {
        if ($property_tax->bukti_bayar && Storage::disk('public')->exists($property_tax->bukti_bayar)) {
            Storage::disk('public')->delete($property_tax->bukti_bayar);
        }

        $nop = $property_tax->nop;
        $tahun = $property_tax->tahun;
        $property_tax->delete();

        return redirect()->route('property-taxes.index')
            ->with('success', "Data PBB NOP {$nop} Tahun {$tahun} berhasil dihapus.");
    }

    public function print(PropertyTax $property_tax): View
    {
        $property_tax->load(['branch', 'asset', 'creator']);

        return view('property-taxes.print', [
            'tax' => $property_tax,
        ]);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
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
                'keterangan',
            ]);
            fputcsv($out, [
                '32.73.010.001.001-0001.0',
                'Jl. Raya Pajajaran No. 88',
                'Bantarjati',
                'Bogor Utara',
                'Kota Bogor',
                'Dian Mustika Spa & Wellness',
                '01.234.567.8-404.000',
                date('Y'),
                '500.00',
                '350.00',
                '12500000',
                '12500000',
                date('Y-m-d'),
                'Gedung Klinik Cabang Bogor',
            ]);
            fclose($out);
        }, 'template-import-pbb.csv', ['Content-Type' => 'text/csv']);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = PropertyTax::with('branch')
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', $request->integer('tahun')))
            ->when($request->filled('status_bayar'), fn ($q) => $q->where('status_bayar', $request->input('status_bayar')))
            ->when($request->filled('kota_kab'), fn ($q) => $q->where('kota_kab', $request->input('kota_kab')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->orderByDesc('tahun')
            ->orderBy('nop')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID',
                'NOP',
                'Alamat OP',
                'Desa / Kelurahan',
                'Kecamatan',
                'Kota / Kabupaten',
                'Nama WP',
                'NPWP',
                'Tahun',
                'Luas Bumi (m2)',
                'Luas Bangunan (m2)',
                'Pajak Terutang (Rp)',
                'SPPT Yang Dibayar (Rp)',
                'Tanggal Bayar',
                'Status Bayar',
                'Cabang',
                'Keterangan',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->nop,
                    $row->alamat_op,
                    $row->desa,
                    $row->kecamatan,
                    $row->kota_kab,
                    $row->nama_wp,
                    $row->npwp,
                    $row->tahun,
                    $row->luas_bumi,
                    $row->luas_bangunan,
                    $row->pajak,
                    $row->sppt_yg_dibayar,
                    $row->tgl_bayar ? $row->tgl_bayar->format('Y-m-d') : '',
                    $row->status_bayar,
                    $row->branch?->name ?? '-',
                    $row->keterangan,
                ]);
            }
            fclose($out);
        }, 'data-pbb-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ], [
            'csv_file.required' => 'File CSV wajib diunggah.',
            'csv_file.mimes' => 'Format file harus berupa CSV atau TXT.',
            'csv_file.max' => 'Ukuran file CSV maksimal 10 MB.',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return back()->withErrors(['csv_file' => 'Gagal membuka file CSV.']);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->withErrors(['csv_file' => 'File CSV kosong atau tidak memiliki header.']);
        }

        // Clean headers: lowercase and trim BOM
        $cleanHeaders = array_map(function ($h) {
            $h = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h);

            return strtolower(trim($h));
        }, $header);

        $headerMap = array_flip($cleanHeaders);

        $successCount = 0;
        $errors = [];
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Skip blank lines
                if (empty(array_filter($row))) {
                    continue;
                }

                $getValue = function ($key) use ($row, $headerMap) {
                    if (isset($headerMap[$key]) && isset($row[$headerMap[$key]])) {
                        $val = trim($row[$headerMap[$key]]);

                        return $val === '' ? null : $val;
                    }

                    return null;
                };

                $nop = $getValue('nop') ?? ($row[0] ?? null);
                $alamatOp = $getValue('alamat_op') ?? ($row[1] ?? null);
                $namaWp = $getValue('nama_wp') ?? ($row[5] ?? null);
                $tahun = (int) ($getValue('tahun') ?? ($row[7] ?? date('Y')));

                if (! $nop || ! $alamatOp || ! $namaWp) {
                    $errors[] = "Baris {$rowNumber}: NOP, Alamat OP, atau Nama WP tidak boleh kosong.";

                    continue;
                }

                $pajak = $getValue('pajak') !== null ? (float) str_replace([',', ' '], '', $getValue('pajak')) : null;
                $bayar = $getValue('sppt_yg_dibayar') !== null ? (float) str_replace([',', ' '], '', $getValue('sppt_yg_dibayar')) : null;
                $tglBayar = $getValue('tgl_bayar');

                PropertyTax::updateOrCreate(
                    [
                        'nop' => $nop,
                        'tahun' => $tahun,
                    ],
                    [
                        'alamat_op' => $alamatOp,
                        'desa' => $getValue('desa'),
                        'kecamatan' => $getValue('kecamatan'),
                        'kota_kab' => $getValue('kota_kab'),
                        'nama_wp' => $namaWp,
                        'npwp' => $getValue('npwp'),
                        'luas_bumi' => $getValue('luas_bumi') !== null ? (float) $getValue('luas_bumi') : null,
                        'luas_bangunan' => $getValue('luas_bangunan') !== null ? (float) $getValue('luas_bangunan') : null,
                        'pajak' => $pajak,
                        'sppt_yg_dibayar' => $bayar,
                        'tgl_bayar' => $tglBayar,
                        'keterangan' => $getValue('keterangan'),
                        'created_by' => auth()->id(),
                    ]
                );

                $successCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            return back()->withErrors(['csv_file' => 'Terjadi kesalahan saat memproses data: '.$e->getMessage()]);
        }

        fclose($handle);

        $msg = "Berhasil mengimpor {$successCount} data PBB.";
        if (! empty($errors)) {
            $msg .= ' Beberapa baris dilewati: '.implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('property-taxes.index')->with('success', $msg);
    }
}
