<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Imports\TransactionImporterInterface;
use App\Exceptions\InvalidTransactionException;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvalidTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_throws_exception_for_invalid_user_type()
    {
        $mockImporter = $this->createMock(TransactionImporterInterface::class);
        $mockImporter->method('import')
            ->willThrowException(InvalidTransactionException::forRow(2, "Invalid user_type 'wrong_type'"));

        $this->expectException(InvalidTransactionException::class);
        $this->expectExceptionMessage("Invalid transaction at line 2: Invalid user_type 'wrong_type'");

        $mockImporter->import('fake-path.csv');
    }

    /** @test */
    public function it_throws_exception_for_invalid_operation_type()
    {
        $mockImporter = $this->createMock(TransactionImporterInterface::class);
        $mockImporter->method('import')
            ->willThrowException(InvalidTransactionException::forRow(3, "Invalid operation_type 'wrong_op'"));

        $this->expectException(InvalidTransactionException::class);
        $this->expectExceptionMessage("Invalid transaction at line 3: Invalid operation_type 'wrong_op'");

        $mockImporter->import('fake-path.csv');
    }

    /** @test */
    public function it_throws_exception_for_invalid_amount()
    {
        $mockImporter = $this->createMock(TransactionImporterInterface::class);
        $mockImporter->method('import')
            ->willThrowException(InvalidTransactionException::forRow(4, "Invalid amount '-100'"));

        $this->expectException(InvalidTransactionException::class);
        $this->expectExceptionMessage("Invalid transaction at line 4: Invalid amount '-100'");

        $mockImporter->import('fake-path.csv');
    }

    /** @test */
    public function it_throws_exception_for_missing_columns()
    {
        $mockImporter = $this->createMock(TransactionImporterInterface::class);
        $mockImporter->method('import')
            ->willThrowException(InvalidTransactionException::forRow(5, "Missing columns"));

        $this->expectException(InvalidTransactionException::class);
        $this->expectExceptionMessage("Invalid transaction at line 5: Missing columns");

        $mockImporter->import('fake-path.csv');
    }
}
