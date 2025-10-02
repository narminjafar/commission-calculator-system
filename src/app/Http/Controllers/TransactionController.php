<?php

namespace App\Http\Controllers;

use App\DTO\TransactionFeeDTO;
use App\Http\Requests\TransactionImportRequest;
use App\Imports\TransactionImporterInterface;
use App\Pipelines\FilterTransactions;
use App\Services\CommissionServiceInterface;
use Illuminate\Pipeline\Pipeline;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionImporterInterface $importer,
        private CommissionServiceInterface $commissionService
    ) {}

    public function calculate(TransactionImportRequest $request)
    {
        $transactions = $this->importer->import($request->file('csv')->getRealPath());

        $filters = $request->only(['user_id', 'user_type', 'operation_type', 'date_from', 'date_to']);

        $filtered = app(Pipeline::class)
            ->send($transactions)
            ->through([new FilterTransactions($filters)])
            ->thenReturn()
            ->get();

        $result = collect($filtered)->map(fn($tx) => TransactionFeeDTO::fromTransaction($tx, $this->commissionService));

        return response()->json($result);
    }
}
