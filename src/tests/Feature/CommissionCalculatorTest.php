<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Transaction;
use App\Models\Amount;
use App\Services\CommissionServiceInterface;
use Carbon\Carbon;

class CommissionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private CommissionServiceInterface $commissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = app(CommissionServiceInterface::class);
    }

    public function test_cash_in_commission_is_capped()
    {
        $amount = Amount::create([
            'value' => 10000000,
            'currency' => 'EUR',
        ]);

        $tx = Transaction::create([
            'date' => Carbon::parse('2016-01-05'),
            'user_type' => 'private',
            'user_id' => 1,
            'operation_type' => 'cash_in',
            'amount_id' => $amount->id,
        ]);

        $fee = $this->commissionService->calculate($tx);

        $this->assertEquals(5.00, $fee);
    }

    public function test_cash_out_private_commission_minimum_applies()
    {
        $amount = Amount::create([
            'value' => 100,
            'currency' => 'EUR',
        ]);

        $tx = Transaction::create([
            'date' => Carbon::parse('2016-01-07'),
            'user_type' => 'private',
            'user_id' => 1,
            'operation_type' => 'cash_out',
            'amount_id' => $amount->id,
        ]);

        $fee = $this->commissionService->calculate($tx);

        $this->assertEquals(0.5, $fee); // minimum tətbiq olunur
    }

    public function test_cash_out_business_flat_fee()
    {
        $amount = Amount::create([
            'value' => 300,
            'currency' => 'EUR',
        ]);

        $tx = Transaction::create([
            'date' => Carbon::parse('2016-01-06'),
            'user_type' => 'business',
            'user_id' => 2,
            'operation_type' => 'cash_out',
            'amount_id' => $amount->id,
        ]);

        $fee = $this->commissionService->calculate($tx);

        $this->assertEquals(1.5, $fee); // 0.5% of 300
    }

    public function test_invalid_row_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $tx = new Transaction([
            'date' => Carbon::parse('2016-01-06'),
            'user_type' => 'unknown', // invalid
            'user_id' => 2,
            'operation_type' => 'invalid_op',
            'amount_id' => null,
        ]);

        $this->commissionService->calculate($tx);
    }
}
