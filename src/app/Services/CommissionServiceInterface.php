<?php

namespace App\Services;

use App\Commissions\CommissionTypeInterface;
use App\Models\Transaction;

interface CommissionServiceInterface
{
    public function calculate(Transaction $transaction): float;
    public function registerStrategy(string $key, CommissionTypeInterface $commissionType): void;
}
