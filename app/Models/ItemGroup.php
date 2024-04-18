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
        'is_activa',
        'status'
    ];

    public function getTopParent($itemgroup)
    {
        if ($itemgroup->parent_id == null)
        {
            return $itemgroup->id;
        }

        $parent = ItemGroup::find($itemgroup->parent_id);

        return $this->getTopParent($parent);
    }

    public function getTopParentName($itemgroup)
    {
        if ($itemgroup->parent_id == null)
        {
            return $itemgroup->name;
        }

        $parent = ItemGroup::find($itemgroup->parent_id);

        return $this->getTopParentName($parent);
    }

    public function getListParent($itemgroup, $name)
    {
        if ($itemgroup->parent_id == null)
        {
            return $name;
        }

        $parent = ItemGroup::find($itemgroup->parent_id);
        $name = $name.' > '.$parent->name;

        return $this->getTopParent($parent, $name);
    }

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

    public function isActiva(){
        $activa = match ($this->is_activa) {
          '1' => 'Ya',
          default => 'Tidak',
        };

        return $activa;
    }

    public function productionType(){
        $production_type = match ($this->production_type) {
          '1' => 'SFG-1',
          '2' => 'SFG-2',
          '3' => 'SFG-3',
          '4' => 'SFG-4',
          '5' => 'SFG-5',
          '6' => 'FG',
          default => ' Tidak',
        };

        return $production_type;
    }
}
