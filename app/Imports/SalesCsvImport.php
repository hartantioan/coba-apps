<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class SalesCsvImport implements ToArray
{
    /**
     * @param array $array
     * @return void
     */
    public function array(array $array)
    {
        // You can manipulate the data here before saving, if necessary
        // In this example, we just return the data as-is
        return $array;
    }
}