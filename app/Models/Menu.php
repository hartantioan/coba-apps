<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Menu extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'menus';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'url',
        'icon',
        'table_name',
        'parent_id',
        'order',
        'status',
        'is_maintenance'
    ];

    public function sub()
    {
        return $this->hasMany('App\Models\Menu', 'parent_id', 'id');
    }

    public function menuCoa()
    {
        return $this->hasMany('App\Models\MenuCoa');
    }

    public function menuUser()
    {
        return $this->hasMany('App\Models\MenuUser');
    }

    public function parentsub(){
        return $this->belongsTo('App\Models\Menu', 'parent_id', 'id')->withTrashed();
    }

    public function childHasChild(){
        $passed = false;
        foreach($this->sub as $row){
            foreach($row->sub as $rowsub){
                $passed = true;
            }
        }

        return $passed;
    }

    public function fullName(){
        $name = '';

        if($this->parentsub()->exists()){
            $parent1 = $this->parentsub;
            if($parent1->parentsub()->exists()){
                $parent2 = $parent1->parentsub;
                if($parent2->parentsub()->exists()){
                    $parent3 = $parent2->parentsub;
                    if($parent3->parentsub()->exists()){
                        $parent4 = $parent3->parentsub;
                        if($parent4->parentsub()->exists()){
                            $name .= $parent4->parentsub->name.' / ';
                        }
                        $name .= $parent4->name.' / ';
                    }
                    $name .= $parent3->name.' / ';
                }
                $name .= $parent2->name.' / ';
            }
            $name .= $parent1->name.' / ';
        }

        $name .= $this->name;

        return $name;
    }

    public function fullUrl(){
        $url = '';

        if($this->parentsub()->exists()){
            $parent1 = $this->parentsub;
            if($parent1->parentsub()->exists()){
                $parent2 = $parent1->parentsub;
                if($parent2->parentsub()->exists()){
                    $parent3 = $parent2->parentsub;
                    if($parent3->parentsub()->exists()){
                        $parent4 = $parent3->parentsub;
                        if($parent4->parentsub()->exists()){
                            $url .= $parent4->parentsub->url.'/';
                        }
                        $url .= $parent4->url.'/';
                    }
                    $url .= $parent3->url.'/';
                }
                $url .= $parent2->url.'/';
            }
            $url .= $parent1->url.'/';
        }

        $url .= $this->url;

        return $url;
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public function isMaintenance(){
        switch($this->is_maintenance) {
            case '1':
                $maintenance = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            default:
                $maintenance = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">In-Active</span>';
                break;
        }

        return $maintenance;
    }

    public function getColumnTypes(){
        $columns = Schema::getColumnListing($this->table_name);

        $arrmenu = [];

        foreach($columns as $row){
            $type = Schema::getColumnType($this->table_name,$row);
            if($type == 'float'){
                $arrmenu[] = [
                    'type'      => $type,
                    'column'    => $row,
                ];
            }
        }

        return $arrmenu;
    }

    public function journalable()
    {
        $columns = Schema::getColumnListing($this->table_name);

        $arrmenu = [];

        foreach($columns as $row){
            $type = Schema::getColumnType($this->table_name,$row);
            if($type == 'float'){
                $arrmenu[] = [
                    'type'      => $type,
                    'column'    => $row,
                ];
            }
        }

        if(count($arrmenu) > 0){
            return true;
        }else{
            return null;
        }
    }
}
