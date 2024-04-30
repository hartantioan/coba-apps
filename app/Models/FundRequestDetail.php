<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FundRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'fund_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'fund_request_id',
        'note',
        'qty',
        'unit_id',
        'price',
        'tax_id',
        'percent_tax',
        'is_include_tax',
        'wtax_id',
        'percent_wtax',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'place_id',
        'line_id',
        'machine_id',
        'division_id',
        'project_id',
    ];

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    }

    public function machine(){
        return $this->belongsTo('App\Models\Machine','machine_id','id')->withTrashed();
    }

    public function division(){
        return $this->belongsTo('App\Models\Division','division_id','id')->withTrashed();
    }

    public function project(){
        return $this->belongsTo('App\Models\Project','project_id','id')->withTrashed();
    }

    public function purchaseInvoiceDetail()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail')->whereHas('purchaseInvoice',function($query){
            $query->whereIn('status',['1','2','3','7']);
        });
    }

    public function balanceInvoice(){
        $total = round($this->grandtotal,2);

        foreach($this->purchaseInvoiceDetail as $row){
            $total -= $row->grandtotal;
        }

        return $total;
    }

    public function totalInvoice(){
        $total = 0;

        foreach($this->purchaseInvoiceDetail as $row){
            $total += $row->grandtotal;
        }

        return $total;
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function taxMaster(){
        return $this->belongsTo('App\Models\Tax','tax_id','id')->withTrashed();
    }

    public function wtaxMaster(){
        return $this->belongsTo('App\Models\Tax','wtax_id','id')->withTrashed();
    }

    public function hasPaymentRequestDetail(){
        return $this->hasMany('App\Models\PaymentRequestDetail','lookable_id','id')->where('lookable_type',$this->table)->whereHas('paymentRequest',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function balancePaymentRequest(){
        $total = $this->grandtotal;

        foreach($this->hasPaymentRequestDetail as $row){
            $total -= $row->nominal;
        }

        return $total;
    }

    public function fundRequest()
    {
        return $this->belongsTo('App\Models\FundRequest', 'fund_request_id', 'id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }

    public function purchaseDownPaymentDetail(){
        return $this->hasMany('App\Models\PurchaseDownPaymentDetail','fund_request_detail_id','id')->whereHas('purchaseDownPayment',function($query){
            $query->whereIn('status',['1','2','3']);
        });
    }

    public function getDataBalanceUsed(){
        $total = $this->fundRequest->grandtotal;
        $used = $this->fundRequest->totalReceivableUsedPaid();
        $bobotUsed = round($used / $total,2);
        $total = $this->total * $bobotUsed;
        $tax = $this->tax * $bobotUsed;
        $wtax = $this->wtax * $bobotUsed;
        $grandtotal = $this->grandtotal * $bobotUsed;
        $qty = $this->qty * $bobotUsed;
        $data['total'] = $total;
        $data['tax'] = $tax;
        $data['wtax'] = $wtax;
        $data['grandtotal'] = $grandtotal;
        $data['qty'] = $qty;

        return $data;
    }
}
