<?php

namespace App\Imports;

interface TransactionImporterInterface
{
    public function import(string $path): array;
}
