<?php

use App\Models\Bank;
use App\Models\Brand;
use App\Models\CommissionRule;
use App\Models\FinancialAccount;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\Supplier;
use App\Models\Unit;

return [
    'entities' => [
        'product-categories' => [
            'title' => 'Product Categories',
            'model' => ProductCategory::class,
            'fields' => [
                'code' => ['label' => 'Code', 'rules' => ['required', 'string'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required', 'string']],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'brands' => [
            'title' => 'Brands',
            'model' => Brand::class,
            'fields' => [
                'code' => ['label' => 'Code', 'rules' => ['required'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'units' => [
            'title' => 'Units',
            'model' => Unit::class,
            'fields' => [
                'code' => ['label' => 'Code', 'rules' => ['required'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'decimal_scale' => ['label' => 'Decimal Scale', 'type' => 'number', 'rules' => ['nullable', 'integer']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'products' => [
            'title' => 'Products',
            'model' => Product::class,
            'fields' => [
                'sku' => ['label' => 'SKU', 'rules' => ['required'], 'unique' => true],
                'barcode' => ['label' => 'Barcode', 'rules' => ['nullable'], 'unique' => true],
                'category_id' => ['label' => 'Category ID', 'type' => 'number', 'rules' => ['required', 'exists:product_categories,id']],
                'brand_id' => ['label' => 'Brand ID', 'type' => 'number', 'rules' => ['nullable', 'exists:brands,id']],
                'unit_id' => ['label' => 'Unit ID', 'type' => 'number', 'rules' => ['required', 'exists:units,id']],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'purchase_price' => ['label' => 'Purchase Price', 'type' => 'number', 'rules' => ['nullable', 'numeric']],
                'default_sell_price' => ['label' => 'Sell Price', 'type' => 'number', 'rules' => ['nullable', 'numeric']],
                'minimum_stock' => ['label' => 'Min Stock', 'type' => 'number', 'rules' => ['nullable', 'numeric']],
                'maximum_stock' => ['label' => 'Max Stock', 'type' => 'number', 'rules' => ['nullable', 'numeric']],
                'weight' => ['label' => 'Weight', 'type' => 'number', 'rules' => ['nullable', 'numeric']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'service-categories' => [
            'title' => 'Service Categories',
            'model' => ServiceCategory::class,
            'fields' => [
                'code' => ['label' => 'Code', 'rules' => ['required'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'services' => [
            'title' => 'Services',
            'model' => Service::class,
            'fields' => [
                'service_code' => ['label' => 'Service Code', 'rules' => ['required'], 'unique' => true],
                'category_id' => ['label' => 'Category ID', 'type' => 'number', 'rules' => ['required', 'exists:service_categories,id']],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'pricing_method' => ['label' => 'Pricing Method', 'type' => 'select', 'options' => ['FIXED', 'DURATION', 'WEIGHT'], 'rules' => ['required']],
                'default_duration_minutes' => ['label' => 'Duration (minutes)', 'type' => 'number', 'rules' => ['nullable', 'integer']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'service-packages' => [
            'title' => 'Service Packages',
            'model' => ServicePackage::class,
            'fields' => [
                'code' => ['label' => 'Code', 'rules' => ['required'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'price' => ['label' => 'Price', 'type' => 'number', 'rules' => ['nullable', 'numeric']],
                'valid_from' => ['label' => 'Valid From', 'type' => 'date'],
                'valid_until' => ['label' => 'Valid Until', 'type' => 'date'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'suppliers' => [
            'title' => 'Suppliers',
            'model' => Supplier::class,
            'fields' => [
                'supplier_code' => ['label' => 'Code', 'rules' => ['required'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'contact_person' => ['label' => 'Contact Person'],
                'phone' => ['label' => 'Phone'],
                'email' => ['label' => 'Email', 'rules' => ['nullable', 'email']],
                'address' => ['label' => 'Address', 'type' => 'textarea'],
                'tax_number' => ['label' => 'NPWP'],
                'payment_terms_days' => ['label' => 'Payment Terms (days)', 'type' => 'number', 'rules' => ['nullable', 'integer']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'banks' => [
            'title' => 'Banks',
            'model' => Bank::class,
            'fields' => [
                'code' => ['label' => 'Code', 'rules' => ['required'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'financial-accounts' => [
            'title' => 'Financial Accounts',
            'model' => FinancialAccount::class,
            'fields' => [
                'account_code' => ['label' => 'Account Code', 'rules' => ['required'], 'unique' => true],
                'account_type' => ['label' => 'Type', 'type' => 'select', 'options' => ['CASH', 'BANK', 'E_WALLET', 'OTHER'], 'rules' => ['required']],
                'bank_id' => ['label' => 'Bank ID', 'type' => 'number', 'rules' => ['nullable', 'exists:banks,id']],
                'branch_id' => ['label' => 'Branch ID', 'type' => 'number', 'rules' => ['nullable', 'exists:branches,id']],
                'account_number' => ['label' => 'Account Number'],
                'account_name' => ['label' => 'Account Name', 'rules' => ['required']],
                'currency' => ['label' => 'Currency', 'rules' => ['nullable', 'string', 'max:3']],
                'opening_balance' => ['label' => 'Opening Balance', 'type' => 'number', 'rules' => ['nullable', 'numeric']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'payment-methods' => [
            'title' => 'Payment Methods',
            'model' => PaymentMethod::class,
            'fields' => [
                'code' => ['label' => 'Code', 'rules' => ['required'], 'unique' => true],
                'name' => ['label' => 'Name', 'rules' => ['required']],
                'method_type' => ['label' => 'Type', 'rules' => ['required']],
                'financial_account_id' => ['label' => 'Financial Account ID', 'type' => 'number', 'rules' => ['nullable', 'exists:financial_accounts,id']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
        'commission-rules' => [
            'title' => 'Commission Rules',
            'model' => CommissionRule::class,
            'fields' => [
                'employee_id' => ['label' => 'Employee ID', 'type' => 'number', 'rules' => ['nullable', 'exists:employees,id']],
                'service_id' => ['label' => 'Service ID', 'type' => 'number', 'rules' => ['nullable', 'exists:services,id']],
                'commission_type' => ['label' => 'Type', 'type' => 'select', 'options' => ['PERCENTAGE', 'FIXED'], 'rules' => ['required']],
                'commission_value' => ['label' => 'Value', 'type' => 'number', 'rules' => ['required', 'numeric']],
                'effective_date' => ['label' => 'Effective Date', 'type' => 'date', 'rules' => ['required', 'date']],
                'end_date' => ['label' => 'End Date', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:effective_date']],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['ACTIVE', 'INACTIVE'], 'rules' => ['required']],
            ],
        ],
    ],
];
