<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRequest extends Model
{
    use HasFactory;

    protected $table = 'chat_requests';

    protected $primaryKey = 'id';

    protected $fillable = ['from_user_id', 'to_user_id', 'status'];

    public function fromUser()
    {
        return $this->belongsTo('App\Models\User', 'from_user_id', 'id')->withTrashed();
    }

    public function toUser()
    {
        return $this->belongsTo('App\Models\User', 'to_user_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
            'Waiting' => 'Menunggu',
            'Approved' => 'Diterima',
            'Rejected' => 'Ditolak',
            default => 'Invalid',
        };

        return $status;
    }
}