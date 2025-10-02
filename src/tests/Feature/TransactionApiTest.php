<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Models\Amount;
use App\Models\Transaction;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_commissions_via_api()
    {
        // CSV test faylı
        $csvContent = implode("\n", [
            "2025-10-01,1,private,cash_in,200,EUR",
            "2025-10-02,2,business,cash_out,500,USD"
        ]);

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->postJson('/api/transactions/calculate', [
            'csv' => $file,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(2, $data);
        $this->assertEquals('EUR', $data[0]['currency']);
        $this->assertEquals('USD', $data[1]['currency']);
    }
}
