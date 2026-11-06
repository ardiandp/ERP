<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name' => Setting::getValue('app.name', 'ERP Dian Mustika'),
            'company_legal_name' => Setting::getValue('company.legal_name', 'PT Dian Mustika Care'),
            'company_phone' => Setting::getValue('company.phone', '08123456789'),
            'company_email' => Setting::getValue('company.email', 'info@dianmustika.com'),
            'company_address' => Setting::getValue('company.address', 'Jakarta, Indonesia'),
            'pos_receipt_header' => Setting::getValue('pos.receipt_header', 'Klinik & Salon Muslimah Dian Mustika'),
            'pos_receipt_footer' => Setting::getValue('pos.receipt_footer', 'Terima kasih atas kunjungan Anda.'),
            'pos_default_tax' => Setting::getValue('pos.default_tax', '0'),
            'inventory_low_stock_threshold' => Setting::getValue('inventory.low_stock_threshold', '5'),
            'attendance_work_start' => Setting::getValue('attendance.work_start', '08:00'),
            'attendance_grace_period_minutes' => Setting::getValue('attendance.grace_period_minutes', '15'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'company_legal_name' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'pos_receipt_header' => ['nullable', 'string', 'max:255'],
            'pos_receipt_footer' => ['nullable', 'string'],
            'pos_default_tax' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'inventory_low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'attendance_work_start' => ['nullable', 'string'],
            'attendance_grace_period_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        Setting::setValue('app.name', $validated['app_name'], 'general');
        Setting::setValue('company.legal_name', $validated['company_legal_name'] ?? '', 'company');
        Setting::setValue('company.phone', $validated['company_phone'] ?? '', 'company');
        Setting::setValue('company.email', $validated['company_email'] ?? '', 'company');
        Setting::setValue('company.address', $validated['company_address'] ?? '', 'company');

        Setting::setValue('pos.receipt_header', $validated['pos_receipt_header'] ?? '', 'pos');
        Setting::setValue('pos.receipt_footer', $validated['pos_receipt_footer'] ?? '', 'pos');
        Setting::setValue('pos.default_tax', $validated['pos_default_tax'] ?? '0', 'pos');

        Setting::setValue('inventory.low_stock_threshold', $validated['inventory_low_stock_threshold'] ?? '5', 'inventory');

        Setting::setValue('attendance.work_start', $validated['attendance_work_start'] ?? '08:00', 'hr');
        Setting::setValue('attendance_grace_period_minutes', $validated['attendance_grace_period_minutes'] ?? '15', 'hr');

        AuditLog::record('update', null, [], $validated);

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
