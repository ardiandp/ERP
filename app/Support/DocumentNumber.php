<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class DocumentNumber
{
    public static function generate(string $prefix, string $modelClass, string $column, ?string $date = null): string
    {
        $date = $date ?? now()->toDateString();
        $stamp = str_replace('-', '', $date);
        $start = "{$prefix}-{$stamp}";

        /** @var Model $model */
        $model = new $modelClass;

        $last = $model->newQuery()
            ->where($column, 'like', "{$start}%")
            ->orderByDesc($column)
            ->value($column);

        $sequence = 1;

        if ($last) {
            $sequence = (int) substr($last, strrpos($last, '-') + 1) + 1;
        }

        return sprintf('%s-%04d', $start, $sequence);
    }
}
