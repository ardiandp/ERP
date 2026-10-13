<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Service;
use App\Models\ServicePackage;

class PricingService
{
    public function productPrice(Product $product): float
    {
        return (float) $product->default_sell_price;
    }

    public function packagePrice(ServicePackage $package): float
    {
        return (float) $package->price;
    }

    public function servicePrice(Service $service, ?int $durationMinutes = null, ?float $weight = null): float
    {
        $today = now()->toDateString();

        $base = $service->prices()
            ->where('effective_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $today);
            });

        $query = match ($service->pricing_method) {
            'DURATION' => (clone $base)->where('duration_minutes', $durationMinutes ?? $service->default_duration_minutes),
            'WEIGHT' => (clone $base)->where('min_weight', '<=', $weight)
                ->where(fn ($q) => $q->where('max_weight', '>=', $weight)->orWhereNull('max_weight')),
            default => clone $base,
        };

        $price = $query->orderByDesc('effective_date')->first()
            ?? $service->prices()->orderByDesc('effective_date')->first();

        return $price ? (float) $price->price : 0.0;
    }
}
