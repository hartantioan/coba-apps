<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_banks';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'bank_id',
        'name',
        'no',
        'branch',
        'is_default'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    public function bank(){
        return $this->belongsTo('App\Models\Bank','bank_id','id');
    }

    public function isDefault(){
        $default = match ($this->is_default) {
          '1' => '<i class="material-icons" style="font-size: inherit !important;color:red;">star</i>',
          '0' => '',
          default => 'Invalid',
        };
        return $default;
    }
}