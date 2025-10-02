<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Transaction;
use App\Models\Amount;
use App\Services\CommissionServiceInterface;
use App\Commissions\CashInCommission;

class CommissionStrategyTest extends TestCase
{
    use RefreshDatabase;

    private CommissionServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CommissionServiceInterface::class);
    }

    /** @test */
    public function it_allows_adding_new_commission_strategy()
    {
        // Yeni strategy əlavə olunur
        $this->service->registerStrategy('custom_strategy', new class implements \App\Commissions\CommissionTypeInterface {
            public function calculate($transaction): float {
                return 42.0;
            }
        });

        $amount = Amount::create(['value' => 1000, 'currency' => 'EUR']);
        $tx = Transaction::create([
            'date' => now(),
            'user_id' => 1,
            'user_type' => 'private',
            'operation_type' => 'custom_strategy',
            'amount_id' => $amount->id,
        ]);

        $fee = $this->service->calculate($tx);

        $this->assertEquals(42.0, $fee);
    }
}
