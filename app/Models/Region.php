<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'regions';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
    ];

    public function getSubdistrict(){
        $arr = [];
        $data = Region::where('code', 'like', "$this->code%")->whereRaw("CHAR_LENGTH(code) = 13")->get();
        foreach($data as $row){
            $arr[] = [
                'id'    => $row->id,
                'code'  => $row->code,
                'name'  => $row->name,
            ];
        }
        return $arr;
    }

    public function getCity(){
        $arr = [];
        $data = Region::where('code', 'like', "$this->code%")->whereRaw("CHAR_LENGTH(code) = 5")->get();
        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
                'subdistrict'   => $row->getSubdistrict(),
            ];
        }
        return $arr;
    }

    public function parentRegion(){
        $arr = explode('.', $this->code);
        $count = count($arr);

        $text = '-';

        if($count == 2){
            $text = Region::where('code', $arr[0])->first()->name;
        }elseif($count == 3){
            $text = Region::where('code', $arr[0])->first()->name.' - '.Region::where('code', $arr[0].'.'.$arr[1])->first()->name;
        }elseif($count == 4){
            $text = Region::where('code', $arr[0])->first()->name.' - '.Region::where('code', $arr[0].'.'.$arr[1])->first()->name.' - '.Region::where('code', $arr[0].'.'.$arr[1].'.'.$arr[2])->first()->name;
        }

        return $text;
    }
}
