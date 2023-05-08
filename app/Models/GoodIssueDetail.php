<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodIssueDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_issue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_issue_id',
        'item_id',
        'qty',
        'price',
        'total',
        'note',
        'coa_id',
        'place_id',
        'department_id',
        'warehouse_id'
    ];

    public function goodIssue()
    {
        return $this->belongsTo('App\Models\GoodReceive', 'good_issue_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function qtyConvertToBuy()
    {
        $qty = round($this->qty / $this->item->buy_convert,3);

        return $qty;
    }
}
