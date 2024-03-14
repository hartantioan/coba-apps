<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AssetGroup extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'asset_groups';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'coa_id',
        'depreciation_coa_id',
        'cost_coa_id',
        'depreciation_period',
        'status'
    ];

    public function costCoa()
    {
        return $this->belongsTo('App\Models\Coa', 'cost_coa_id', 'id')->withTrashed();
    }

    public function parentSub(){
        return $this->belongsTo('App\Models\AssetGroup', 'parent_id', 'id');
    }

    public function childSub(){
        return $this->hasMany('App\Models\AssetGroup', 'parent_id', 'id');
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id');
    }

    public function depreciationCoa(){
        return $this->belongsTo('App\Models\Coa', 'depreciation_coa_id', 'id');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }
}
