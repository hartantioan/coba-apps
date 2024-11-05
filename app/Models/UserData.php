<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_datas';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'npwp',
        'tax_type',
        'address',
        'country_id',
        'province_id',
        'city_id',
        'district_id',
        'is_default',
    ];

    public function isDefault(){
        $default = match ($this->is_default) {
          '1' => '<i class="material-icons" style="font-size: inherit !important;color:red;">star</i>',
          '0' => '',
          default => 'Invalid',
        };
        return $default;
    }

    public function taxType(){
        $default = match ($this->tax_type) {
          '1' => 'Normal',
          '2' => 'Perdagangan Bebas',
          default => 'Normal',
        };
        return $default;
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function country(){
        return $this->belongsTo('App\Models\Country','country_id','id');
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id');
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id');
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id');
    }
}
