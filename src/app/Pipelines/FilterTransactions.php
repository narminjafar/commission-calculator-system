<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilterTransactions
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function handle($transactions, Closure $next)
    {
        $filters = $this->filters;

        $apply = function ($query) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($key === 'date_from') {
                    $query = $query instanceof Builder
                        ? $query->where('date', '>=', $value)
                        : $query->filter(fn($tx) => $tx->date >= $value);
                } elseif ($key === 'date_to') {
                    $query = $query instanceof Builder
                        ? $query->where('date', '<=', $value)
                        : $query->filter(fn($tx) => $tx->date <= $value);
                } else {
                    $query = $query instanceof Builder
                        ? $query->where($key, $value)
                        : $query->where($key, $value);
                }
            }
            return $query;
        };

        $transactions = $apply($transactions);

        return $next($transactions);
    }
}
