<?php

namespace App\Commissions;

use App\Models\Transaction;

class CashOutBusinessCommission implements CommissionTypeInterface
{
    private float $rate = 0.005; // 0.5%

    public function calculate(Transaction $tx): float
    {
        return round($tx->amount->value * $this->rate, 2);
    }
}
