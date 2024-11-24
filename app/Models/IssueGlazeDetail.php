<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class IssueGlazeDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'issue_glaze_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'issue_glaze_id',
        'lookable_type',
        'lookable_id',
        'note',
        'qty',
        'unit_id',
        'place_id',
        'warehouse_id',
        'item_stock_id',
        'total'
    ];

    public function typeItem(){
        $type = match ($this->lookable_type) {
            'items' => 'Item',
            default => 'Manual',
        };

        return $type;
    }

    public function issueGlaze(){
        return $this->belongsTo('App\Models\IssueGlaze', 'issue_glaze_id', 'id')->withTrashed();
    }
    public function lookable()
    {
        return $this->morphTo();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->table)->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
