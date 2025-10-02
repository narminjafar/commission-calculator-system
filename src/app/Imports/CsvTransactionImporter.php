<?php

namespace App\Imports;

use App\Models\Transaction;
use App\Models\Amount;
use App\Exceptions\ParseException;
use App\Exceptions\InvalidTransactionException;

class CsvTransactionImporter implements TransactionImporterInterface
{
    public function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new ParseException("File not found: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $transactions = [];

        foreach ($lines as $lineNo => $line) {
            $parts = str_getcsv($line);

            if (count($parts) < 6) {
                throw InvalidTransactionException::forRow($lineNo + 1, 'Missing columns');
            }

            [$date, $userId, $userType, $operationType, $amountStr, $currency] = $parts;

            if (!in_array($userType, ['private', 'business'])) {
                throw InvalidTransactionException::forRow($lineNo + 1, "Invalid user_type '{$userType}'");
            }

            if (!in_array($operationType, ['withdraw', 'deposit', 'loan_repayment'])) {
                throw InvalidTransactionException::forRow($lineNo + 1, "Invalid operation_type '{$operationType}'");
            }

            if (!is_numeric($amountStr) || (float)$amountStr <= 0) {
                throw InvalidTransactionException::forRow($lineNo + 1, "Invalid amount '{$amountStr}'");
            }

            if (!$currency || strlen($currency) !== 3) {
                throw InvalidTransactionException::forRow($lineNo + 1, "Invalid currency '{$currency}'");
            }

            $amount = Amount::create([
                'value' => (float)$amountStr,
                'currency' => $currency,
            ]);

            $transactions[] = Transaction::create([
                'date' => $date,
                'user_id' => (int)$userId,
                'user_type' => $userType,
                'operation_type' => $operationType,
                'amount_id' => $amount->id,
            ]);
        }

        return $transactions;
    }
}
