<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RetirementDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'retirement_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'retirement_id',
        'asset_id',
        'qty',
        'unit_id',
        'retirement_nominal',
        'note',
        'coa_id',
    ];

    public function retirement(){
        return $this->belongsTo('App\Models\Retirement', 'retirement_id', 'id')->withTrashed();
    }

    public function asset(){
        return $this->belongsTo('App\Models\Asset', 'asset_id', 'id')->withTrashed();
    }

    public function unit(){
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }
    public function coa(){
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }
}
