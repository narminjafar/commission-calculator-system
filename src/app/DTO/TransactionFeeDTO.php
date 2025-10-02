<?php

namespace App\DTO;

use App\Models\Transaction;
use App\Services\CommissionServiceInterface;

class TransactionFeeDTO
{
    public int $transaction_id;
    public float $fee;
    public string $currency;

    public function __construct(int $transaction_id, float $fee, string $currency)
    {
        $this->transaction_id = $transaction_id;
        $this->fee = $fee;
        $this->currency = $currency;
    }

    public static function fromTransaction(Transaction $transaction, CommissionServiceInterface $service): self
    {
        $fee = $service->calculate($transaction);
        $currency = $transaction->amount->currency;

        return new self(
            transaction_id: $transaction->id,
            fee: $fee,
            currency: $currency
        );
    }
}
