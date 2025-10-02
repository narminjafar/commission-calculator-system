<?php

namespace App\Commissions;

use App\Models\Transaction;

class CashOutPrivateCommission implements CommissionTypeInterface
{
    private float $rate = 0.003; // 0.3%
    private float $min = 0.5;

    public function calculate(Transaction $tx): float
    {
        $fee = $tx->amount->value * $this->rate;
        return $fee < $this->min ? $this->min : round($fee, 2);
    }
}
