<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ResidenceDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'residence_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'residence_id',
        'region_id',
    ];

    public function residence(){
        return $this->belongsTo('App\Models\Residence', 'residence_id', 'id')->withTrashed();
    }

    public function region(){
        return $this->belongsTo('App\Models\Region', 'region_id', 'id')->withTrashed();
    }
}
