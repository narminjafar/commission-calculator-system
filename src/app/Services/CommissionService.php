<?php

namespace App\Services;

use App\Commissions\CommissionTypeInterface;
use App\Models\Transaction;
use App\Services\CommissionServiceInterface;

class CommissionService implements CommissionServiceInterface
{
    private array $types = [];

    public function registerStrategy(string $key, CommissionTypeInterface $commissionType): void
    {
        $this->types[$key] = $commissionType;
    }

    public function calculate(Transaction $tx): float
    {
        $key = $this->resolveKey($tx);
        if (!isset($this->types[$key])) {
            throw new \RuntimeException("No strategy registered for $key");
        }
        return $this->types[$key]->calculate($tx);
    }

    private function resolveKey(Transaction $tx): string
    {
        if ($tx->operation_type === 'withdraw') {
            return "cash_out_{$tx->user_type}"; // private / business
        }

        if ($tx->operation_type === 'deposit') {
            return 'cash_in';
        }

        if ($tx->operation_type === 'loan_repayment') {
            return 'loan_repayment';
        }

          throw new \RuntimeException("No strategy registered for {$tx->operation_type}");
    }
}
