<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemPricelist extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_pricelists';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'user_id',
        'type_id',
        'group_id',
        'place_id',
        'grade_id',
        'customer_id',
        'brand_id',
        'type_delivery',
        'start_date',
        'end_date',
        'price',
        'status',
    ];
    public function type(){
        return $this->belongsTo('App\Models\Type','type_id','id')->withTrashed();
    }

    public function grade(){
        return $this->belongsTo('App\Models\Grade','grade_id','id')->withTrashed();
    }

    public function customer(){
        return $this->belongsTo('App\Models\User','customer_id','id')->withTrashed();
    }
    public function brand(){
        return $this->belongsTo('App\Models\Brand','brand_id','id')->withTrashed();
    }
    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function deliveryType(){
        $type = match ($this->type_delivery) {
            '1' => 'LOCO',
            '2' => 'FRANCO',
          default => 'Invalid',
        };

        return $type;
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function group(){
        return $this->belongsTo('App\Models\Group','group_id','id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

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
