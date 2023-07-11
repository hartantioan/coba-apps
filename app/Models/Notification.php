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

    public function lookable(){
        return $this->morphTo();
    }

    public function getURL(){
        $menu = Menu::where('table_name',$this->lookable_type)->first();
        return $menu->fullUrl();
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

    public function getTimeAgo()
    {
        $time_difference = time() - strtotime($this->created_at);

        if( $time_difference < 1 ) { return 'less than 1 second ago'; }
        $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                    30 * 24 * 60 * 60       =>  'month',
                    24 * 60 * 60            =>  'day',
                    60 * 60                 =>  'hour',
                    60                      =>  'minute',
                    1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;

            if( $d >= 1 )
            {
                $t = round( $d );
                return 'sekitar ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' yang lalu';
            }
        }
    }
}
