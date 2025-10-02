<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Services
use App\Services\{
    CommissionServiceInterface,
    CommissionService
};

// Importers
use App\Imports\{
    TransactionImporterInterface,
    CsvTransactionImporter
};

// Commissions
use App\Commissions\{
    CashInCommission,
    CashOutPrivateCommission,
    CashOutBusinessCommission,
    LoanRepaymentCommission
};

class CommissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CommissionServiceInterface::class, function ($app) {
            $service = new CommissionService();

            $service->registerStrategy('cash_in', $app->make(CashInCommission::class));
            $service->registerStrategy('cash_out_private', $app->make(CashOutPrivateCommission::class));
            $service->registerStrategy('cash_out_business', $app->make(CashOutBusinessCommission::class));
            $service->registerStrategy('loan_repayment', $app->make(LoanRepaymentCommission::class));

            return $service;
        });

        $this->app->bind(CashInCommission::class, CashInCommission::class);
        $this->app->bind(CashOutPrivateCommission::class, CashOutPrivateCommission::class);
        $this->app->bind(CashOutBusinessCommission::class, CashOutBusinessCommission::class);
        $this->app->bind(LoanRepaymentCommission::class, LoanRepaymentCommission::class);

        $this->app->bind(TransactionImporterInterface::class, CsvTransactionImporter::class);
    }

    public function boot(): void
    {
        // boot logic lazım deyil burada
    }
}
