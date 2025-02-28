<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SampleTestQcPackingResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sample_test_qc_packing_results';

    protected $fillable = [
        'sample_test_input_id',
        'user_id',
        'dry_whiteness_value',
        'document',
        'note',
        'status',
    ];

    protected $dates = ['deleted_at'];

    public function attachment()
    {
        if($this->document){
            $arr = explode(',',$this->document);
            $arrDoc = [];
            foreach($arr as $key => $row){
                if(Storage::exists($row)){
                    $arrDoc[] = '<a href="'.asset(Storage::url($row)).'" target="_blank">Lampiran '.($key + 1).'</a>';
                }
            }
            $document_po = implode(' ',$arrDoc);
        }else{
            $document_po = 'Tidak ada';
        }

        return $document_po;
    }
    public function sampleTestInput()
    {
        return $this->belongsTo(SampleTestInput::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
