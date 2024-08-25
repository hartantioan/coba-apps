<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DeliveryCostStandard extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'delivery_cost_standards';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'category_transportation',
        'city_id',
        'district_id',
        'price',
        'start_date',
        'end_date',
        'note',
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id')->withTrashed();
    }

    public function categoryTransportation(){
        $type = match ($this->category_transportation) {
            '1' => 'Tronton',
            '2' => 'Colt Diesel',
            default => '',
        };
  
        return $type;
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
        $type = match ($this->status) {
          '1' => 'Active',
          '2' => 'Non Active',
          default => '',
        };

        return $type;
    }

}
