<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['chat_request_id','from_user_id', 'to_user_id', 'chat_message', 'message_status'];

    public function fromUser()
    {
        return $this->belongsTo('App\Models\User', 'from_user_id', 'id')->withTrashed();
    }

    public function toUser()
    {
        return $this->belongsTo('App\Models\User', 'to_user_id', 'id')->withTrashed();
    }

    public function chatRequest()
    {
        return $this->belongsTo('App\Models\ChatRequest', 'chat_request_id', 'id');
    }

    public function getTimeAgo()
    {
        $time_difference = time() - strtotime($this->updated_at);

        if( $time_difference < 1 ) { return '< 1 detik'; }
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
                return $t . ' ' . $str . ' ago';
            }
        }
    }
}
