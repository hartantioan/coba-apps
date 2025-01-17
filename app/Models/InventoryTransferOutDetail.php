<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferOutDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventory_transfer_out_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'inventory_transfer_out_id',
        'item_stock_id',
        'item_id',
        'qty',
        'price',
        'total',
        'note',
        'area_id',
    ];

    public function inventoryTransferOut()
    {
        return $this->belongsTo('App\Models\InventoryTransferOut', 'inventory_transfer_out_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id')->withTrashed();
    }

    public function itemStock()
    {
        return $this->belongsTo('App\Models\ItemStock', 'item_stock_id', 'id');
    }

    public function landedCostDetail()
    {
        return $this->hasMany('App\Models\LandedCostDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('landedCost',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->table)->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function listSerial(){
        $arr = [];
        foreach($this->itemSerial as $row){
            $arr[] = $row->serial_number;
        }

        return implode(', ',$arr);
    }

    public function listSerialIn(){
        $arr = [];
        foreach($this->itemSerialIn as $row){
            $arr[] = $row->serial_number;
        }

        return implode(', ',$arr);
    }

    public function arrSerial(){
        $arr = [];
        
        foreach($this->itemSerial as $row){
            $arr[] = [
                'serial_id'     => $row->id,
                'serial_number' => $row->serial_number,
            ];
        }

        return $arr;
    }

    public function arrSerialIn(){
        $arr = [];
        
        foreach($this->itemSerialIn as $row){
            $arr[] = [
                'serial_id'     => $row->id,
                'serial_number' => $row->serial_number,
            ];
        }

        return $arr;
    }

    public function itemSerial(){
        return $this->hasMany('App\Models\ItemSerial','usable_id','id')->where('usable_type',$this->table);
    }

    public function itemSerialIn(){
        return $this->hasMany('App\Models\ItemSerial','lookable_id','id')->where('lookable_type',$this->table);
    }
}
