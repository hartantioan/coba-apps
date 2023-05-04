<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_order_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_order_id',
        'purchase_request_detail_id',
        'item_id',
        'coa_id',
        'qty',
        'price',
        'percent_discount_1',
        'percent_discount_2',
        'discount_3',
        'subtotal',
        'note',
        'is_tax',
        'is_include_tax',
        'percent_tax',
        'is_wtax',
        'percent_wtax',
        'tax_id',
        'wtax_id',
        'place_id',
        'department_id',
        'warehouse_id',
    ];

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function isTax(){
        $type = match ($this->is_tax) {
          NULL => 'Tidak',
          '1' => 'Ya',
          default => 'Invalid',
        };

        return $type;
    }

    public function isWtax(){
        $type = match ($this->is_wtax) {
          NULL => 'Tidak',
          '1' => 'Ya',
          default => 'Invalid',
        };

        return $type;
    }

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'purchase_order_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa','coa_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function department(){
        return $this->belongsTo('App\Models\Department','department_id','id')->withTrashed();
    }

    public function tax(){
        return $this->belongsTo('App\Models\Tax','tax_id','id')->withTrashed();
    }

    public function wtax(){
        return $this->belongsTo('App\Models\Tax','wtax_id','id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id')->withTrashed();
    }

    public function goodReceiptDetail(){
        return $this->hasMany('App\Models\GoodReceiptDetail','purchase_order_detail_id','id')->whereHas('goodReceipt',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function getBalanceReceipt()
    {
        $item = $this->item_id;
        $po = $this->purchase_order_id;

        $received = $this->goodReceiptDetail()->sum('qty');

        $balance = $this->qty - $received;

        return $balance;
    }

    public function purchaseRequestDetail()
    {
        return $this->belongsTo('App\Models\PurchaseRequestDetail','purchase_request_detail_id','id');
    }
}
