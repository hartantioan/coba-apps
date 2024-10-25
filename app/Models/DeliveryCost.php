<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DeliveryCost extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'delivery_costs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'transportation_id',
        'name',
        'valid_from',
        'valid_to',
        'from_city_id',
        'from_subdistrict_id',
        'to_city_id',
        'to_subdistrict_id',
        'tonnage',
        'qty_tonnage',
        'ritage',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function transportation(){
        return $this->belongsTo('App\Models\Transportation','transportation_id','id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function fromCity(){
        return $this->belongsTo('App\Models\Region','from_city_id','id')->withTrashed();
    }

    public function fromSubdistrict(){
        return $this->belongsTo('App\Models\Region','from_subdistrict_id','id')->withTrashed();
    }

    public function toCity(){
        return $this->belongsTo('App\Models\Region','to_city_id','id')->withTrashed();
    }

    public function toSubdistrict(){
        return $this->belongsTo('App\Models\Region','to_subdistrict_id','id')->withTrashed();
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
    public function statusRaw(){

        $status = match ($this->status) {
            '1' => 'Aktif',
            '2' => 'Tidak Aktif',

            default => 'Invalid',
        };

        return $status;
    }
}
