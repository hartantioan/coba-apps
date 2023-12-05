<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Psy\CodeCleaner\ImplicitReturnPass;

class ItemGroup extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_groups';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'coa_id',
        'production_type',
        'status'
    ];

    public function itemGroupWarehouse(){
        return $this->hasMany('App\Models\ItemGroupWarehouse');
    }

    public function listWarehouse(){
        $arr = [];
        foreach($this->itemGroupWarehouse as $row){
            $arr[] = $row->warehouse->name;
        }

        return implode(',',$arr);
    }

    public function parentSub(){
        return $this->belongsTo('App\Models\ItemGroup', 'parent_id', 'id')->withTrashed();
    }

    public function childSub(){
        return $this->hasMany('App\Models\ItemGroup', 'parent_id', 'id');
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function productionType(){
        $production_type = match ($this->production_type) {
          '1' => 'SFG-1',
          '2' => 'SFG-2',
          '3' => 'SFG-3',
          '4' => 'FG',
          default => ' Tidak',
        };

        return $production_type;
    }
}
