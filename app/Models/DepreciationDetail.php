<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DepreciationDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'depreciation_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'depreciation_id',
        'asset_id',
        'nominal',
    ];

    public function depreciation(){
        return $this->belongsTo('App\Models\Depreciation', 'depreciation_id', 'id')->withTrashed();
    }

    public function asset(){
        return $this->belongsTo('App\Models\Asset', 'asset_id', 'id')->withTrashed();
    }

    public function depreciationNumber(){
        $data = Depreciation::whereHas('depreciationDetail', function($query){
            $query->where('asset_id',$this->asset_id)->where('id','<=',$this->id);
        })->whereIn('status',['2','3'])->orderByDesc('period')->count();

        return $data;
    }
}
