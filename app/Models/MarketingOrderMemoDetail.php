<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderMemoDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_memo_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'marketing_order_memo_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'is_include_tax',
        'percent_tax',
        'tax_id',
        'total',
        'tax',
        'grandtotal',
        'note',
    ];

    public function marketingOrderMemo()
    {
        return $this->belongsTo('App\Models\MarketingOrderMemo', 'marketing_order_memo_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }

    public function getCode(){
        $code = '';
        if($this->lookable_type == 'marketing_order_down_payments'){
            $code = $this->lookable->code;
        }elseif($this->lookable_type == 'marketing_order_invoice_details'){
            $code = $this->lookable->marketingOrderInvoice->code;
        }elseif($this->lookable_type == 'coas'){
            $code = $this->lookable->code.' - '.$this->lookable->name;
        }

        return $code;
    }

    public function getDate(){
        $date = '-';
        if($this->lookable_type == 'marketing_order_down_payments'){
            $date = date('d/m/y',strtotime($this->lookable->post_date));
        }elseif($this->lookable_type == 'marketing_order_invoice_details'){
            $date = date('d/m/y',strtotime($this->lookable->marketingOrderInvoice->post_date));
        }

        return $date;
    }

    public function getId(){
        $id = '-';
        if($this->lookable_type == 'marketing_order_down_payments'){
            $id = $this->lookable->id;
        }elseif($this->lookable_type == 'marketing_order_invoice_details'){
            $id = $this->lookable->marketingOrderInvoice->id;
        }elseif($this->lookable_type == 'coas'){
            $id = '';
        }

        return $id;
    }

    public function getType(){
        $type = '';
        if($this->lookable_type == 'marketing_order_down_payments'){
            $type = $this->lookable_type;
        }elseif($this->lookable_type == 'marketing_order_invoice_details'){
            $type = $this->lookable->marketingOrderInvoice->getTable();
        }elseif($this->lookable_type == 'coas'){
            $type = '';
        }

        return $type;
    }

    public function marketingOrderDownPayment(){
        if($this->lookable_type == 'marketing_order_down_payments'){
            return true;
        }else{
            return false;
        }
    }

    public function marketingOrderInvoiceDetail(){
        if($this->lookable_type == 'marketing_order_invoice_details'){
            return true;
        }else{
            return false;
        }
    }
}
