<?php

namespace App\Commissions;

use App\Models\Transaction;

interface CommissionTypeInterface
{
    public function calculate(Transaction $transaction): float;
}
