<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Announcement extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'announcements';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'description',
        'menu_id',
        'status',
        'start_date',
        'end_date',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function menuMany(){
        $menu_temp = explode(',', $this->menu_id);
        $query_menu = Menu::whereIn('id', $menu_temp)->get();
        $x = "";
        foreach($query_menu as $key=>$row_menu){
            if($key == 0){
                $x .= $row_menu->name;
            }else{
                $x .= ', '.$row_menu->name;
            }
          
        }
        return $x;
    }

    public function menu(){
        return $this->belongsTo('App\Models\Menu', 'menu_id', 'id');
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
}
