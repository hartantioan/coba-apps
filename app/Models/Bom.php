<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'boms';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'user_id',
        'item_id',
        'place_id',
        'qty_output',
        'qty_planned',
        'type',
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function bomDetail(){
        return $this->hasMany('App\Models\BomDetail');
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

    public function type(){
        switch($this->type) {
            case '1':
                $status = 'Perakitan';
                break;
            case '2':
                $status = 'Penjualan';
                break;
            case '3':
                $status = 'Produksi';
                break;
            case '4':
                $status = 'Template';
                break;
            default:
                $status = 'Invalid';
                break;
        }

        return $status;
    }
}
