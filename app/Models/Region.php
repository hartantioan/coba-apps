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

    public function getNewCode(){
        $code = $this->code;

        $arr = explode('.',$this->code);

        if(count($arr) == 1){
            $latestRegion = Region::selectRaw('RIGHT(code, 2) as code')->where('code','like',"$code%")->whereRaw("CHAR_LENGTH(code) = 5")->orderByDesc('code')->get();
            if($latestRegion->count() > 0) {
                $code = (int)$latestRegion[0]->code + 1;
            } else {
                $code = '01';
            }
    
            $no = str_pad($code, 2, 0, STR_PAD_LEFT);

            $code .= '.'.$no;
        }elseif(count($arr) == 2){

        }elseif(count($arr) == 3){

        }elseif(count($arr) == 4){

        }
    }
}
