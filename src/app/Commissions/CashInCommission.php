<?php

namespace App\Commissions;

use App\Models\Transaction;

class CashInCommission implements CommissionTypeInterface
{
    private float $rate = 0.0003; // 0.03%
    private float $max = 5.0;

    public function calculate(Transaction $tx): float
    {
        $fee = $tx->amount->value * $this->rate;
        return $fee > $this->max ? $this->max : round($fee, 2);
    }
}
