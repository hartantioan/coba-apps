<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentRequestDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'payment_request_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'payment_request_id',
        'lookable_type',
        'lookable_id',
        'cost_distribution_id',
        'coa_id',
        'nominal',
        'note',
        'remark',
        'place_id',
        'warehouse_id',
        'line_id',
        'machine_id',
        'department_id',
        'project_id',
    ];

    public function line()
    {
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id')->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Division', 'department_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function paymentRequest()
    {
        return $this->belongsTo('App\Models\PaymentRequest', 'payment_request_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function costDistribution()
    {
        return $this->belongsTo('App\Models\CostDistribution', 'cost_distribution_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }

    public function purchaseDownPayment()
    {
        if($this->lookable_type == 'purchase_down_payments'){
            return true;
        }else{
            return false;
        }
    }

    public function getMemo(){
        $total = 0;

        if($this->lookable_type == 'purchase_down_payments'){
            $total = $this->lookable->totalMemo();
        }

        if($this->lookable_type == 'purchase_invoices'){
            $total = $this->lookable->totalMemo();
        }
        
        return $total;
    }
 
    public function purchaseInvoice()
    {
        if($this->lookable_type == 'purchase_invoices'){
            return true;
        }else{
            return false;
        }
    }

    public function fundRequest()
    {
        if($this->lookable_type == 'fund_requests'){
            return true;
        }else{
            return false;
        }
    }

    public function fundRequestDetail()
    {
        if($this->lookable_type == 'fund_request_details'){
            return true;
        }else{
            return false;
        }
    }

    public function marketingOrderMemo()
    {
        if($this->lookable_type == 'marketing_order_memos'){
            return true;
        }else{
            return false;
        }
    }

    public function type(){
        $type = match ($this->lookable_type) {
            'fund_requests'             => 'Permohonan Dana',
            'fund_request_details'      => 'Permohonan Dana',
            'purchase_invoices'         => 'A/P Invoice',
            'purchase_down_payments'    => 'AP Down Payment',
            'coas'                      => 'Coa Biaya',
            'marketing_order_memos'     => 'AR Memo',
            default                     => 'Belum ditentukan',
          };
  
          return $type;
    }

    public function getAccountCode(){
        $code = match ($this->lookable_type) {
            'fund_requests'             => $this->lookable->account->employee_no,
            'purchase_invoices'         => $this->lookable->account->employee_no,
            'purchase_down_payments'    => $this->lookable->supplier->employee_no,
            'marketing_order_memos'     => $this->lookable->account->employee_no,
            default                     => '',
          };
  
          return $code;
    }

    public function getCode(){
        $code = match ($this->lookable_type) {
            'fund_requests'             => $this->lookable->code,
            default                     => $this->lookable->code,
          };
  
          return $code;
    }

    public function totalOutgoingUsedWeight(){
        $nominal = $this->nominal;
        $totalUsed = 0;
        foreach($this->paymentRequest->outgoingPayment->paymentRequestCross as $rowcross){
            $totalUsed += $rowcross->nominal;
        }

        foreach($this->paymentRequest->outgoingPayment->closeBillDetail as $rowclose){
            $totalUsed += $rowclose->nominal;
        }

        $result = round(($nominal / $this->paymentRequest->total) * $totalUsed,2);

        return $result;
    }

    public function totalOutgoingUsedWeightByDate($date){
        $nominal = $this->nominal;
        $totalUsed = 0;
        foreach($this->paymentRequest->outgoingPayment->paymentRequestCross()->whereHas('paymentRequest',function($query)use($date){
            $query->whereIn('status',['2','3'])->whereDate('post_date','<=',$date);
        })->get() as $rowcross){
            $totalUsed += $rowcross->nominal;
        }

        foreach($this->paymentRequest->outgoingPayment->closeBillDetail()->whereHas('closeBill',function($query)use($date){
            $query->whereIn('status',['2','3'])->whereDate('post_date','<=',$date);
        })->get() as $rowclose){
            $totalUsed += $rowclose->nominal;
        }

        $result = round(($nominal / $this->paymentRequest->total) * $totalUsed,2);

        return $result;
    }

    public function totalIncomingUsedWeight(){
        $nominal = $this->nominal;
        $totalUsed = 0;
        foreach($this->paymentRequest->outgoingPayment->incomingPaymentDetail as $rowincoming){
            $totalUsed += $rowincoming->total;
        }

        $result = round(($nominal / $this->paymentRequest->total) * $totalUsed,2);

        return $result;
    }

    public function totalIncomingUsedWeightByDate($date){
        $nominal = $this->nominal;
        $totalUsed = 0;
        foreach($this->paymentRequest->outgoingPayment->incomingPaymentDetail()->whereHas('incomingPayment',function($query)use($date){
            $query->whereDate('post_date','<=',$date);
        })->get() as $rowincoming){
            $totalUsed += $rowincoming->total;
        }

        $result = round(($nominal / $this->paymentRequest->total) * $totalUsed,2);

        return $result;
    }

    public function totalWeightAdmin(){
        $admin = $this->paymentRequest->admin;
        $newNominal = ($this->nominal / $this->paymentRequest->total) * $admin;
        return $newNominal;
    }

    public function totalWeightRounding(){
        $rounding = $this->paymentRequest->rounding;
        $newNominal = ($this->nominal / $this->paymentRequest->total) * $rounding;
        return $newNominal;
    }
}
