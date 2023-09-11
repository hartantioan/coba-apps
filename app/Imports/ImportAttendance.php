<?php

namespace App\Imports;

use App\Models\Attendances;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportAttendance implements ToModel
{
   /**
     * @param array $row
     * @return Attendances|null
     */
    public function model(array $row)
    {
        $verifyType = ($row[6] === 'FP') ? 1 : (($row[6] === 'PW') ? 3 : null);

        if ($verifyType !== null) {
            return new Attendances([
                'employee_no' => $row[1],
                'date' =>  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[3]),
                'attendance_machine_id' => $row[4],
                'verify_type' => $verifyType,
            ]);
        }

        return null; 
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
