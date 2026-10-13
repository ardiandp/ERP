<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\Warehouse;
use App\Services\PricingService;
use App\Services\SalesService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class SalesController extends Controller
{
    public function __construct(
        protected SalesService $sales,
        protected PricingService $pricing,
    ) {}

    public function index(Request $request)
    {
        $transactions = SalesTransaction::with('customer')
            ->when($request->filled('search'), fn ($query) => $query->where('invoice_number', 'like', '%'.$request->input('search').'%'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', ['transactions' => $transactions]);
    }

    public function create()
    {
        return view('sales.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'transaction_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ref' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        try {
            $transaction = $this->sales->create($this->withPayment($data), $this->mapItems($request->input('items')));
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('sales.show', $transaction)->with('success', 'Transaksi berhasil disimpan.');
    }

    public function show(SalesTransaction $sale)
    {
        $sale->load(['customer', 'branch', 'items.product', 'items.service', 'items.package', 'items.employee', 'payments.paymentMethod']);

        return view('sales.show', [
            'transaction' => $sale,
            'paymentMethods' => PaymentMethod::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    protected function withPayment(array $data): array
    {
        if (! empty($data['payment_method_id']) && ! empty($data['paid_amount'])) {
            $data['payment'] = [
                'payment_method_id' => $data['payment_method_id'],
                'amount' => $data['paid_amount'],
                'payment_date' => $data['transaction_date'] ?? null,
            ];
        }

        return $data;
    }

    protected function mapItems(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            if (empty($row['ref'])) {
                continue;
            }

            [$type, $id] = array_pad(explode(':', $row['ref'], 2), 2, null);
            $id = $id ? (int) $id : null;

            $items[] = [
                'item_type' => $type,
                'product_id' => $type === 'PRODUCT' ? $id : null,
                'service_id' => $type === 'SERVICE' ? $id : null,
                'package_id' => $type === 'PACKAGE' ? $id : null,
                'quantity' => $row['quantity'] ?? 1,
                'unit_price' => $row['unit_price'] ?? 0,
                'discount' => $row['discount'] ?? 0,
                'employee_id' => $row['employee_id'] ?? null,
            ];
        }

        return $items;
    }

    protected function formData(): array
    {
        return [
            'branches' => Branch::where('status', 'ACTIVE')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'warehouses' => Warehouse::where('status', 'ACTIVE')->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('status', 'ACTIVE')->orderBy('name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
            'catalog' => $this->catalog(),
        ];
    }

    protected function catalog(): array
    {
        $catalog = [];

        foreach (Product::where('status', 'ACTIVE')->orderBy('name')->get() as $product) {
            $catalog["PRODUCT:{$product->id}"] = [
                'label' => "[Produk] {$product->name}",
                'price' => $this->pricing->productPrice($product),
                'type' => 'PRODUCT',
            ];
        }

        foreach (Service::where('status', 'ACTIVE')->orderBy('name')->get() as $service) {
            $catalog["SERVICE:{$service->id}"] = [
                'label' => "[Layanan] {$service->name}",
                'price' => $this->pricing->servicePrice($service),
                'type' => 'SERVICE',
            ];
        }

        foreach (ServicePackage::where('status', 'ACTIVE')->orderBy('name')->get() as $package) {
            $catalog["PACKAGE:{$package->id}"] = [
                'label' => "[Paket] {$package->name}",
                'price' => $this->pricing->packagePrice($package),
                'type' => 'PACKAGE',
            ];
        }

        return $catalog;
    }
}
