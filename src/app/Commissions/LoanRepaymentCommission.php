<?php

namespace App\Commissions;

use App\Models\Transaction;

class LoanRepaymentCommission implements CommissionTypeInterface
{
    private float $rate = 0.02; // 2%
    private float $fixed = 1.0;

    public function calculate(Transaction $tx): float
    {
        return round($tx->amount->value * $this->rate + $this->fixed, 2);
    }
}
