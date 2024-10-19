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
        $total = round($this->total,2);

        foreach($this->purchaseInvoiceRealDetail as $rowinvoice){
            $total -= round($rowinvoice->total,2);
        }

        return $total;
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->purchaseInvoiceRealDetail as $rowinvoice){
            $total += round($rowinvoice->total,2);
        }

        return $total;
    }

    public function balanceInvoiceByDate($date){
        $total = round($this->total,2);

        foreach($this->purchaseInvoiceRealDetail()->whereHas('purchaseInvoice',function($query)use($date){
            $query->where('post_date','<=',$date);
        })->get() as $rowinvoice){
            $total -= round($rowinvoice->total,2);
        }

        return $total;
    }

    public function totalInvoiceByDate($date){
        $total = 0;

        foreach($this->purchaseInvoiceRealDetail()->whereHas('purchaseInvoice',function($query)use($date){
            $query->where('post_date','<=',$date);
        })->get() as $rowinvoice){
            $total += round($rowinvoice->total,2);
        }

        return $total;
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->table)->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
