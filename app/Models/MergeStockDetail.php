<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MergeStockDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'merge_stock_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'merge_stock_id',
        'item_id',
        'item_stock_id',
        'qty',
        'total'
    ];

    public function mergeStock(){
        return $this->belongsTo('App\Models\MergeStock', 'merge_stock_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id');
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->table)->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
