<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Transaction;
use App\Models\Amount;
use App\Pipelines\FilterTransactions;
use Illuminate\Pipeline\Pipeline;
use Carbon\Carbon;

class TransactionFilterPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected Amount $amount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->amount = Amount::create(['currency' => 'EUR', 'value' => 200]);

        Transaction::create([
            'date' => Carbon::parse('2025-10-01'),
            'user_type' => 'private',
            'user_id' => 1,
            'operation_type' => 'cash_in',
            'amount_id' => $this->amount->id,
        ]);

        Transaction::create([
            'date' => Carbon::parse('2025-10-02'),
            'user_type' => 'private',
            'user_id' => 2,
            'operation_type' => 'cash_out',
            'amount_id' => $this->amount->id,
        ]);

        Transaction::create([
            'date' => Carbon::parse('2025-10-03'),
            'user_type' => 'business',
            'user_id' => 1,
            'operation_type' => 'cash_out',
            'amount_id' => $this->amount->id,
        ]);

        Transaction::create([
            'date' => Carbon::parse('2025-10-04'),
            'user_type' => 'business',
            'user_id' => 2,
            'operation_type' => 'cash_in',
            'amount_id' => $this->amount->id,
        ]);
    }

    /** @test */
    public function it_filters_transactions_by_user_id()
    {
        $filters = ['user_id' => 1];

        $filtered = app(Pipeline::class)
            ->send(Transaction::all())
            ->through([new FilterTransactions($filters)])
            ->thenReturn();

        $this->assertCount(2, $filtered);
        $this->assertTrue($filtered->pluck('user_id')->contains(1));
    }

    /** @test */
    public function it_filters_transactions_by_user_type()
    {
        $filters = ['user_type' => 'business'];

        $filtered = app(Pipeline::class)
            ->send(Transaction::all())
            ->through([new FilterTransactions($filters)])
            ->thenReturn();

        $this->assertCount(2, $filtered);
        $this->assertTrue($filtered->pluck('user_type')->every(fn($v) => $v === 'business'));
    }

    /** @test */
    public function it_filters_transactions_by_operation_type()
    {
        $filters = ['operation_type' => 'cash_in'];

        $filtered = app(Pipeline::class)
            ->send(Transaction::all())
            ->through([new FilterTransactions($filters)])
            ->thenReturn();

        $this->assertCount(2, $filtered);
        $this->assertTrue($filtered->pluck('operation_type')->every(fn($v) => $v === 'cash_in'));
    }

    /** @test */
    public function it_filters_transactions_by_date_range()
    {
        $filters = [
            'date_from' => '2025-10-02',
            'date_to' => '2025-10-03'
        ];

        $filtered = app(Pipeline::class)
            ->send(Transaction::all())
            ->through([new FilterTransactions($filters)])
            ->thenReturn();

        $this->assertCount(2, $filtered);
        $dates = $filtered->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->all();
        $this->assertEquals(['2025-10-02','2025-10-03'],$dates);
    }

    /** @test */
    public function it_applies_multiple_filters_simultaneously()
    {
        $filters = [
            'user_id' => 1,
            'user_type' => 'private',
            'operation_type' => 'cash_in',
            'date_from' => '2025-10-01',
            'date_to' => '2025-10-02'
        ];

        $filtered = app(Pipeline::class)
            ->send(Transaction::all())
            ->through([new FilterTransactions($filters)])
            ->thenReturn();

        $this->assertCount(1, $filtered);
        $tx = $filtered->first();
        $this->assertEquals(1, $tx->user_id);
        $this->assertEquals('private', $tx->user_type);
        $this->assertEquals('cash_in', $tx->operation_type);
        $this->assertEquals('2025-10-01', $tx->date->format('Y-m-d'));
    }

      /** @test */
    public function it_filters_transactions_by_all_criteria()
    {
        $amount1 = Amount::create(['value' => 100, 'currency' => 'EUR']);
        $amount2 = Amount::create(['value' => 200, 'currency' => 'USD']);

        $tx1 = Transaction::create([
            'date' => '2025-10-01',
            'user_id' => 1,
            'user_type' => 'private',
            'operation_type' => 'cash_in',
            'amount_id' => $amount1->id,
        ]);

        $tx2 = Transaction::create([
            'date' => '2025-10-05',
            'user_id' => 2,
            'user_type' => 'business',
            'operation_type' => 'cash_out',
            'amount_id' => $amount2->id,
        ]);

        $tx3 = Transaction::create([
            'date' => '2025-10-10',
            'user_id' => 1,
            'user_type' => 'private',
            'operation_type' => 'cash_out',
            'amount_id' => $amount1->id,
        ]);

        $filters = [
            'user_id' => 1,
            'user_type' => 'private',
            'operation_type' => 'cash_out',
            'date_from' => '2025-10-01',
            'date_to' => '2025-10-15',
        ];

        $filtered = app(Pipeline::class)
            ->send(Transaction::query())
            ->through([new FilterTransactions($filters)])
            ->thenReturn()
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($tx3->id, $filtered->first()->id);
    }
}
