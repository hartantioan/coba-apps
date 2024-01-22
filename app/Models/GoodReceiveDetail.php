<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodReceiveDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_receive_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_receive_id',
        'item_id',
        'qty',
        'price',
        'total',
        'note',
        'coa_id',
        'place_id',
        'line_id',
        'machine_id',
        'department_id',
        'warehouse_id',
        'area_id',
        'item_shading_id',
        'project_id',
    ];

    public function goodReceive()
    {
        return $this->belongsTo('App\Models\GoodReceive', 'good_receive_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function itemShading(){
        return $this->belongsTo('App\Models\ItemShading','item_shading_id','id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
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

    public function itemSerial(){
        return $this->hasMany('App\Models\ItemSerial','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function listSerial(){
        $arr = [];
        foreach($this->itemSerial as $row){
            $arr[] = $row->serial_number;
        }

        return implode(',',$arr);
    }
}
