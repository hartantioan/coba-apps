<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LandedCostFeeDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'landed_cost_fee_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'landed_cost_id',
        'landed_cost_fee_id',
        'total',
        'is_include_tax',
        'percent_tax',
        'percent_wtax',
        'tax',
        'wtax',
        'grandtotal',
    ];

    public function landedCost()
    {
        return $this->belongsTo('App\Models\LandedCost', 'landed_cost_id', 'id')->withTrashed();
    }

    public function landedCostFee()
    {
        return $this->belongsTo('App\Models\LandedCostFee', 'landed_cost_fee_id', 'id')->withTrashed();
    }

    public function purchaseInvoiceDetail()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseInvoice',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function purchaseInvoiceRealDetail()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('purchaseInvoice',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function balanceInvoice(){
        $total = round($this->grandtotal,2);

        foreach($this->purchaseInvoiceRealDetail as $rowinvoice){
            $total -= round($rowinvoice->grandtotal,2);
        }

        return $total;
    }
}
