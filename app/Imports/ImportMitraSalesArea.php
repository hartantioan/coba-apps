<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\MitraSalesArea;
use App\Models\User;
use App\Models\MitraApiSyncData;

class ImportMitraSalesArea implements WithMultipleSheets
{
    public function sheets(): array
    {
        activity()
        ->performedOn(new MitraSalesArea())
        ->causedBy(session('bo_id'))
        ->withProperties('')
        ->log('Add / edit from excel item data.');

        return [
            0 => new handleMainSheet(),
        ];
    }

}

class handleMainSheet implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            $check = null;
            $row = $row->toArray();
            if(isset($row['code']) && $row['code']){
                $check = MitraSalesArea::withTrashed()->where('code',$row['code'])->first();
                //get mitra object
                $mitra = User::where('employee_no',explode('#',$row['mitra'])[0])->first();

                if(!$check){
                    //Insert to table
                    $query = MitraSalesArea::create([
                        'code'     => $row['code'],
                        'name'     => $row['name'],
                        'type'     => $row['type'],
                        'mitra_id' => $mitra->id,
                        'status'   => '1',
                    ]);

                    //Insert to API Data Sync Table
                    $payload = [
                        'code' => $row['code'],
                        'name' => $row['name'],
                        'type' => $row['type'],
                    ];
                    $query->mitraApiSyncDatas()->create([
                        'mitra_id'  => $query->mitra->id,
                        'operation' => 'store',
                        'payload'   => json_encode($payload),
                        'status'    => '0',
                        'attempts'  => 0,
                    ]);

                }else{
                    //Update data in table
                    // if(!$check->hasChildDocument()){
                        if ($check->trashed()) {
                            $check->restore();
                        }
                        $check->name = $row['name'];
                        $check->type = $row['type'];
                        $check->mitra = $mitra ? $mitra->id : NULL;
                        $check->save();

                        //Update to API
                        
                    // }
                }
            }
            else{
                return null;
            }
            DB::commit();
        }catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
        }
    }

    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
