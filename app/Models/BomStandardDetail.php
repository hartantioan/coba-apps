<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BomStandardDetail extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'bom_standard_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'bom_standard_id',
        'lookable_type',
        'lookable_id',
        'cost_distribution_id',
        'qty',
        'nominal',
        'total',
        'description',
    ];

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }

    public function bomStandard(){
        return $this->belongsTo('App\Models\BomStandard', 'bom_standard_id', 'id');
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function type(){
        $type = match ($this->lookable_type) {
            'items'     => 'ITEM',
            'resources' => 'RESOURCE',
            default     => 'INVALID',
        };
  
        return $type;
    }

    public function name(){
        $type = match ($this->lookable_type) {
            'items'     => $this->lookable->code.' - '.$this->lookable->name,
            'resources' => $this->lookable->code.' - '.$this->lookable->name,
            default     => 'INVALID',
        };
  
        return $type;
    }

    public function item(){
        if($this->lookable_type == 'items'){
            return $this->belongsTo('App\Models\Item', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function coa(){
        if($this->lookable_type == 'coas'){
            return $this->belongsTo('App\Models\Coa', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function resource(){
        if($this->lookable_type == 'resources'){
            return $this->belongsTo('App\Models\Resource', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }
}
