<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Notification extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'menu_id',
        'from_user_id',
        'to_user_id',
        'lookable_type',
        'lookable_id',
        'title',
        'note',
        'status'
    ];

    public function fromUser()
    {
        return $this->belongsTo('App\Models\User', 'from_user_id', 'id')->withTrashed();
    }

    public function toUser()
    {
        return $this->belongsTo('App\Models\User', 'to_user_id', 'id')->withTrashed();
    }

    public function icon()
    {
        $menu = Menu::where('table_name',$this->lookable_type)->first();

        if($menu){
            return $menu->icon;
        }else{
            return 'radio_button_unchecked';
        }
    }

    public function menu()
    {
        return $this->belongsTo('App\Models\Menu', 'menu_id', 'id')->withTrashed();
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="amber medium-small white-text padding-3">Menunggu</span>';
                break;
            case '2':
                $status = '<span class="cyan medium-small white-text padding-3">Terbaca</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }
}
