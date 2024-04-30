<?php

namespace App\Imports;

use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportEmployeeSchedule implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows )
    {
        $dataFormatted = [];
        
        foreach ($rows as $key => $row) {
            // Skip the first row (index 0)
            if ($key === 0) {
                continue;
            }

            $data = [
                'A' => $row[0] ?? null,
                'B' => $row[1] ?? null,
                'C' => $row[2] ?? null,
                'D' => $row[3] ?? null,
            ];

            // Find the last non-null column index from E to Z (or any number of columns)
            $lastColumnIndex = 4;
            while (isset($row[$lastColumnIndex]) && !empty($row[$lastColumnIndex])) {
                $lastColumnIndex++;
            }

            // Read from E to the last non-null column
            for ($i = 4; $i < $lastColumnIndex; $i++) {
                $data["E-Z"][$i - 4] = $row[$i];
               
            }
           
            $dataFormatted[] = $data;
        }
        $arrayDate = $dataFormatted[0]["E-Z"];
  
        $dates = [];
        foreach ($arrayDate as $timestamp) {
            $dateTime = DateTime::createFromFormat('U', ($timestamp - 25569) * 86400);
            $dateFormatted = $dateTime->format('d/m/Y');
            $dates[] = $dateFormatted;
        }
       
        $data_shift_schedule = [];
        foreach($dataFormatted as $keyd =>$data_employee){
            
            // if ($key === 0) {
            //     info($key);
            //     break;
            // }
            if($keyd > 0){
                foreach($dates as $index=>$datee){
                    
                    $data_shift_schedule[]=[
                        "user_code" => $data_employee["B"],
                        "shift_code" => $data_employee["E-Z"][$index],
                        "date" => $datee,
                    ];
                   
                }
            }
            
            
        }
       
        foreach($data_shift_schedule as $data_masuk){
            $query_employee=User::where('employee_no',$data_masuk["user_code"])->first();
            $query_shift=Shift::where('code','like','%'.$data_masuk["shift_code"])->first();
        
            if($query_employee){
                if($query_shift){
                    try {
                        $formattedDate = DateTime::createFromFormat('d/m/Y', $data_masuk["date"])->format('Y-m-d');
                        $timestamp = strtotime($formattedDate);
                        DB::beginTransaction();
                       
                        $query = EmployeeSchedule::create([
                            'shift_id'          => $query_shift->id,
                            'date'	            => $formattedDate,
                            'user_id'           => $data_masuk["user_code"],
                            'status'            => '1'
                            
                        ]);
                
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }
            }
        }
        
        // Process the formatted data as needed
        // For example, you can save it to a database or perform any other actions
        // $dataFormatted contains the formatted data

        // Sample output of $dataFormatted:
        // [
        //     [
        //         'A' => '1',
        //         'B' => 'Mons',
        //         'C' => 'it staff',
        //         'D' => 'C123111',
        //         'E-Z' => ['08:00-17:00', '08:30-16:00'],
        //     ],
        //     ...
        // ]

        // You can perform any further actions here or return the data if needed.

    }
}
