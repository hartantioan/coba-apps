<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class StandardCustomerPrice extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'standard_customer_prices';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'group_id',
        'user_id',
        'price',
        'start_date',
        'end_date',
        'note',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function group(){
        return $this->belongsTo('App\Models\Group','group_id','id')->withTrashed();
    }

    public function statusRaw(){
        
        $status = match ($this->status) {
            '1' => 'Aktif',
            '2' => 'Tidak Aktif',

            default => 'Invalid',
        };

        return $status;
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
