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
        'address',
        'country_id',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
    ];

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

    public function subdistrict(){
        return $this->belongsTo('App\Models\Region','subdistrict_id','id');
    }
}