<?php

namespace App\Exceptions;

use Exception;

class InvalidTransactionException extends Exception
{
    public static function forRow(int $lineNo, string $message = ''): self
    {
        $msg = "Invalid transaction at line {$lineNo}";
        if ($message) {
            $msg .= ": {$message}";
        }
        return new self($msg);
    }
}
