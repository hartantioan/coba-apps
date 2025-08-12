<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class StoreItemPriceList extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'store_item_pricelists';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'user_id',
        'item_id',
        'start_date',
        'end_date',
        'price',
        'discount',
        'qty_discount',
        'sell_price',
        'status',
    ];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
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
