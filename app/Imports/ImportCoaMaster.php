<?php

namespace App\Imports;

use App\Models\Coa;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportCoaMaster implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            $coa = Coa::where('code',$row[1])->first();
            if($coa){
                $coa->update([
                    'kode_program_lama' => trim($row[0]),
                ]);
            }
        }
    }
}