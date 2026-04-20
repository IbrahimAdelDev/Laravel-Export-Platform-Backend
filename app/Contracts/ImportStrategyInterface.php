<?php

namespace App\Contracts;

interface ImportStrategyInterface
{
    // This method will be called by the master job to start the import process for a specific sheet and type of data
    public function startImport($batch, $sheetName, $mapping, $extraData);
}