<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Imports\CsvTransactionImporter;
use App\Models\Transaction;
use App\Models\Amount;
use App\Exceptions\InvalidTransactionException;

class TransactionImporterTest extends TestCase
{
    use RefreshDatabase;

    private string $csvPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Test CSV fayl yaradılır
        $this->csvPath = storage_path('app/test_transactions.csv');
        file_put_contents($this->csvPath, implode("\n", [
            "2025-10-01,1,private,cash_in,200,EUR",
            "2025-10-02,2,business,cash_out,500,USD"
        ]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->csvPath)) {
            unlink($this->csvPath);
        }
    }

    /** @test */
    public function it_imports_transactions_from_csv()
    {
        $importer = new CsvTransactionImporter();
        $transactions = $importer->import($this->csvPath);

        $this->assertCount(2, $transactions);

        $this->assertDatabaseHas('transactions', [
            'user_id' => 1,
            'user_type' => 'private',
            'operation_type' => 'cash_in',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => 2,
            'user_type' => 'business',
            'operation_type' => 'cash_out',
        ]);

        // Amount yoxlaması
        $this->assertDatabaseHas('amounts', ['value' => 200, 'currency' => 'EUR']);
        $this->assertDatabaseHas('amounts', ['value' => 500, 'currency' => 'USD']);
    }

    /** @test */
    public function it_throws_exception_for_invalid_csv_row()
    {
        $invalidCsvPath = storage_path('app/invalid_transactions.csv');
        file_put_contents($invalidCsvPath, "2025-10-01,1,private\n"); // incomplete row

        $importer = new CsvTransactionImporter();

        $this->expectException(InvalidTransactionException::class);
        $importer->import($invalidCsvPath);

        unlink($invalidCsvPath);
    }
}
