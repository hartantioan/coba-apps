<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrderDeliveryProcess extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_delivery_processes';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'code',
        'company_id',
        'account_id',
        'marketing_order_delivery_id',
        'post_date',
        'receive_date',
        'return_date',
        'user_driver_id',
        'driver_name',
        'driver_hp',
        'vehicle_name',
        'vehicle_no',
        'weight_netto',
        'note_internal',
        'note_external',
        'status',
        'status_tracking',
        'document',
        'total',
        'tax',
        'rounding',
        'grandtotal',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
    ];

    public function getTypePayment(){
        $type = '';
        foreach($this->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
            $type = $row->marketingOrderDetail->marketingOrder->payment_type;
        }
        return $type;
    }

    public function getPoCustomer(){
        $arr = [];
        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            if(!in_array($row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->document_no,$arr)){
                if($row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->document_no){
                    
                $arr[] = $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->document_no;
                }
            }
        }

        if(count($arr) == 0){
            $arr[]='-';
        }
        return implode(', ',$arr);
    }

    public function getOutlet(){
        $arr = [];
        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            if($row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->outlet()->exists()){
                if(!in_array($row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->outlet->name,$arr)){
                    $arr[] = $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->outlet->name;
                }
            }
        }
        if(count($arr) == 0){
            $arr[]='-';
        }
        return implode(', ',$arr);
    }

    public function getProject(){
        $arr = [];
        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            if($row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->project()->exists()){
                if(!in_array($row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->project->name,$arr)){
                    $arr[] = $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->project->name;
                }
            }
        }
        if(count($arr) == 0){
            $arr[]='-';
        }
        return implode(', ',$arr);
    }

    public function getSalesOrderCode(){
        $arr = [];
        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            if(!in_array($row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->code,$arr)){
                $arr[] = $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->code;
            }
        }
        return implode(', ',$arr);
    }

    public function getNote() {
        $text = 'BASED ON SALES ORDER '.$this->getSalesOrderCode().'. BASEN ON MARKETING ORDER DELIVERY '.$this->marketingOrderDelivery->code.'. BASED ON DELIVERY ORDER '.$this->code.'.';
        return $text;
    }

    public function totalQty(){
        $total = 0;
        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            $total += $row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion;
        }
        return $total;
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function attachment() 
    {
        if($this->document !== NULL && Storage::exists($this->document)) {
            $document = asset(Storage::url($this->document));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }

    public function getPlace(){
        return substr($this->code,7,2);
    }

    public function getWarehouse(){
        $arr = [];
        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            if(!in_array($row->itemStock->warehouse->name,$arr)){
                $arr[] = $row->itemStock->warehouse->name;
            }
        }
        if(count($arr) == 0){
            $arr[]='-';
        }
        return implode(', ',$arr);
    }

    public function deleteFile(){
		if(Storage::exists($this->document)) {
            Storage::delete($this->document);
        }
	}

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function marketingOrderInvoice(){
        return $this->hasOne('App\Models\MarketingOrderInvoice')->whereIn('status',['1','2','3']);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function userDriver()
    {
        return $this->belongsTo('App\Models\UserDriver', 'user_driver_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function marketingOrderDelivery()
    {
        return $this->belongsTo('App\Models\MarketingOrderDelivery', 'marketing_order_delivery_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="amber medium-small white-text padding-3">Menunggu</span>',
          '2' => '<span class="cyan medium-small white-text padding-3">Proses</span>',
          '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
          '6' => '<span class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function statusRaw(){
        $status = match ($this->status) {
            '1' => 'Menunggu',
            '2' => 'Proses',
            '3' => 'Selesai',
            '4' => 'Ditolak',
            '5' => 'Ditutup',
            '6' => 'Direvisi',
            default => 'Invalid',
        };

        return $status;
    }

    public function statusTracking(){
        $status = $this->marketingOrderDeliveryProcessTrack()->orderByDesc('status')->first();

        if($status){
            return $status->status();
        }else{
            return 'Status tracking tidak ditemukan.';
        }
    }

    public function statusTrackingRaw(){
        $status = $this->marketingOrderDeliveryProcessTrack()->orderByDesc('status')->first();

        if ($status) {
            $xstatus = match ($status->status) { 
                '1' => 'Dokumen dibuat',
                '2' => 'Barang dikirimkan',
                '3' => 'Barang sampai di cust',
                '5' => 'SJ kembali ke admin',
                default => 'Invalid',
            };
            return $xstatus;
        } else {
            return 'Status tracking tidak ditemukan.';
        }
    }

    public function statusTrackingDate() {
        $status = $this->marketingOrderDeliveryProcessTrack()->orderByDesc('status')->first();
        if ($status) {
            $xstatus = match ($status->status) { 
                '1', '2', '3' => $status->updated_at,
                '5' => $this->return_date,
                default => 'Invalid',
            };
            return $xstatus;
        } else {
            return 'Status tracking tidak ditemukan.';
        }
    }
    

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = MarketingOrderDeliveryProcess::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return substr($prefix,0,9).'-'.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
        }
    }

    public function isItemSent(){
        $status = false;
        $count = $this->marketingOrderDeliveryProcessTrack()->where('status','2')->count();
        if($count > 0){
            $status = true;
        }
        return $status;
    }

    public function isDelivered(){
        $status = false;
        $count = $this->marketingOrderDeliveryProcessTrack()->where('status','3')->count();
        if($count > 0){
            $status = true;
        }
        return $status;
    }

    public function isReturnedSj(){
        $status = false;
        $count = $this->marketingOrderDeliveryProcessTrack()->where('status','5')->count();
        if($count > 0){
            $status = true;
        }
        return $status;
    }

    public function marketingOrderDeliveryProcessTrack(){
        return $this->hasMany('App\Models\MarketingOrderDeliveryProcessTrack','marketing_order_delivery_process_id','id');
    }

    public function marketingOrderDeliveryProcessDetail(){
        return $this->hasMany('App\Models\MarketingOrderDeliveryProcessDetail','marketing_order_delivery_process_id','id');
    }

    public function getSendStatusTracking(){
        $status = false;

        $data = $this->marketingOrderDeliveryProcessTrack()->whereIn('status',['2','3','4','5'])->count();

        if($data > 0){
            $status = true;
        }

        return $status;
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetail');
    }

    public function getArrStatusTracking(){
        $arr = $this->marketingOrderDeliveryProcessTrack()->orderBy('status')->pluck('status')->toArray();

        return $arr;
    }

    public function hasDetailMatrix(){
        $ada = false;
        if($this->approval()){
            foreach($this->approval() as $row){
                if($row->approvalMatrix()->exists()){
                    $ada = true;
                }
            }
        }

        return $ada;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            if($row->marketingOrderReturnDetail()->exists()){
                $hasRelation = true;
            }
        }

        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            if($row->marketingOrderInvoiceDetail()->exists()){
                $hasRelation = true;
            }
        }

        return $hasRelation;
    }

    public function updateJournal(){
        $journal = Journal::where('lookable_type',$this->table)->where('lookable_id',$this->id)->first();
        
        if($journal){
            foreach($this->marketingOrderDeliveryProcessDetail as $row){
                $total = $row->getHpp();

                $row->update([
                    'total'     => $total
                ]);

                foreach($row->journalDetail as $rowjournal){
                    $rowjournal->update([
                        'nominal_fc'  => $total,
                        'nominal'     => $total,
                    ]);
                }
            }
        }
    }

    public function journal(){
        return $this->hasMany('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function balanceInvoice(){
        $total = 0;

        foreach($this->marketingOrderDeliveryProcessDetail as $row){
            $total += $row->balanceInvoice();
        }

        return $total;
    }

    public function createInvoice(){
        #linkkan ke AR Invoice
        $query = MarketingOrderDeliveryProcess::find($this->id);
        $passed = true;
        foreach($query->marketingOrderDeliveryProcessDetail as $row){
            if($row->marketingOrderInvoiceDetail()->exists()){
                $passed = false;
            }
        }
        if($passed){
            if($query->marketingOrderDelivery->customer->is_ar_invoice){
                $total = 0;
                $tax = 0;
                $grandtotal = 0;
                $downpayment = 0;
                $arrDownPayment = [];
                $passedDp = false;
                $passedTaxSeries = false;
                $subtotal = 0;
                $percent_dp = 0;
                $tax_id = 0;
                $tax_no = '';
                
                foreach($query->marketingOrderDeliveryProcessDetail as $row){
                    $subtotal += $row->getTotal();
                    $tax += $row->getTax();
                    $grandtotal += $row->getGrandtotal();
                    $percent_dp = $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->percent_dp;
                    $tax_id = $row->marketingOrderDeliveryDetail->marketingOrderDetail->tax_id;
                }

                if($percent_dp > 0){
                    $tempDownpayment = $total;
                    $tempBalance = $tempDownpayment;
                    foreach($query->marketingOrderDelivery->customer->marketingOrderDownPayment()->orderBy('code')->get() as $row){
                        if($tempBalance > 0){
                            $balanceInvoice = $row->balanceInvoice();
                            if($balanceInvoice > 0){
                                $nominal = 0;
                                if($tempBalance > $balanceInvoice){
                                    $nominal = $balanceInvoice;
                                }else{
                                    $nominal = $tempBalance;
                                }
                                $arrDownPayment[] = [
                                    'id'            => $row->id,
                                    'type'          => $row->getTable(),
                                    'code'          => $row->code,
                                    'is_include_tax'=> $row->is_include_tax,
                                    'percent_tax'   => $row->percent_tax,
                                    'tax_id'        => $row->tax_id,
                                    'total'         => $nominal,
                                    'tax'           => $nominal * ($row->percent_tax / 100),
                                    'grandtotal'    => $nominal + ($nominal * ($row->percent_tax / 100)),
                                ];
                                $downpayment += $nominal;
                                $tempBalance -= $nominal;
                            }
                        }
                    }
                    if($tempBalance > 0){
                        $passedDp = false;
                    }else{
                        $passedDp = true;
                    }
                }else{
                    $passedDp = true;
                }

                if($passedDp){
                    $arrayTax = TaxSeries::getTaxCode($this->company_id,$this->post_date,'010');
                    if($arrayTax['status'] == 200){
                        $tax_no = $arrayTax['no'];
                    }
                    $total = $subtotal - $downpayment;
                    $menu = Menu::where('table_name','marketing_order_invoices')->first();
                    $prefixCode = $menu->document_code;
                    $code = MarketingOrderInvoice::generateCode($prefixCode.date('y',strtotime($query->return_date)).substr($query->code,7,2));
                    $dueDate = date('Y-m-d', strtotime($query->return_date. ' + '.$query->marketingOrderDelivery->customer->top.' days'));
                    $querymoi = MarketingOrderInvoice::create([
                        'code'			                => $code,
                        'user_id'		                => $query->user_id,
                        'account_id'                    => $query->marketingOrderDelivery->customer_id,
                        'company_id'                    => $query->company_id,
                        'marketing_order_delivery_process_id' => $this->id,
                        'post_date'                     => $query->return_date,
                        'due_date'                      => $dueDate,
                        'document_date'                 => $query->return_date,
                        'status'                        => '1',
                        'type'                          => $query->marketingOrderDelivery->getTypePayment(),
                        'subtotal'                      => $subtotal,
                        'downpayment'                   => $downpayment,
                        'total'                         => $total,
                        'tax'                           => $tax,
                        'grandtotal'                    => $grandtotal,
                        'document'                      => NULL,
                        'tax_no'                        => $tax_no,
                        'tax_id'                        => $tax_id,
                        'note'                          => 'Dibuat otomatis setelah Surat Jalan No. '.$query->code,
                    ]);

                    foreach($query->marketingOrderDeliveryProcessDetail as $key => $rowdata){
                        MarketingOrderInvoiceDetail::create([
                            'marketing_order_invoice_id'    => $querymoi->id,
                            'lookable_type'                 => $rowdata->getTable(),
                            'lookable_id'                   => $rowdata->id,
                            'qty'                           => $rowdata->qty * $rowdata->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                            'price'                         => $rowdata->marketingOrderDeliveryDetail->marketingOrderDetail->realPriceAfterGlobalDiscount(),
                            'is_include_tax'                => $rowdata->marketingOrderDeliveryDetail->marketingOrderDetail->is_include_tax,
                            'percent_tax'                   => $rowdata->marketingOrderDeliveryDetail->marketingOrderDetail->percent_tax,
                            'tax_id'                        => $rowdata->marketingOrderDeliveryDetail->marketingOrderDetail->tax_id,
                            'total'                         => $rowdata->getTotal(),
                            'tax'                           => $rowdata->getTax(),
                            'grandtotal'                    => $rowdata->getGrandtotal(),
                            'note'                          => $rowdata->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->code.' - '.$rowdata->marketingOrderDeliveryDetail->marketingOrderDelivery->code.' - '.$query->code,
                        ]);
                    }

                    foreach($arrDownPayment as $rowdata){
                        MarketingOrderInvoiceDetail::create([
                            'marketing_order_invoice_id'    => $querymoi->id,
                            'lookable_type'                 => $rowdata['type'],
                            'lookable_id'                   => $rowdata['id'],
                            'qty'                           => 1,
                            'price'                         => $rowdata['total'],
                            'is_include_tax'                => $rowdata['is_include_tax'],
                            'percent_tax'                   => $rowdata['percent_tax'],
                            'tax_id'                        => $rowdata['tax_id'],
                            'total'                         => $rowdata['total'],
                            'tax'                           => $rowdata['tax'],
                            'grandtotal'                    => $rowdata['grandtotal'],
                            'note'                          => $rowdata['code'],
                        ]);
                    }
    
                    CustomHelper::sendApproval($querymoi->getTable(),$querymoi->id,$querymoi->note_internal.' - '.$querymoi->note_external);
                    CustomHelper::sendNotification($querymoi->getTable(),$querymoi->id,'Pengajuan AR Invoice No. '.$querymoi->code,$querymoi->note_internal.' - '.$querymoi->note_external,$query->user_id);
    
                    activity()
                        ->performedOn(new MarketingOrderInvoice())
                        ->causedBy($query->user_id)
                        ->withProperties($querymoi)
                        ->log('Add / edit AR Invoice.');
                }
            }
            #end linkkan ke AR Invoice
        }
    }

    public function createJournalSentDocument(){
        $modp = $this;
			
        $query = Journal::create([
            'user_id'		=> $modp->user_id,
            'company_id'    => $modp->company_id,
            'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($modp->post_date)).'00'),
            'lookable_type'	=> $modp->getTable(),
            'lookable_id'	=> $modp->id,
            'post_date'		=> $modp->post_date,
            'note'			=> $modp->note_internal.' - '.$modp->note_external,
            'status'		=> '3'
        ]);
        
        $coabdp = Coa::where('code','100.01.04.05.01')->where('company_id',$modp->company_id)->first();

        foreach($modp->marketingOrderDeliveryProcessDetail as $row){
            $hpp = $row->getHpp();

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'account_id'	=> $coabdp->bp_journal ? $modp->marketingOrderDelivery->customer_id : NULL,
                'coa_id'		=> $coabdp->id,
                'place_id'		=> $row->itemStock->place_id,
                'item_id'		=> $row->itemStock->item_id,
                'warehouse_id'	=> $row->itemStock->warehouse_id,
                'type'			=> '1',
                'nominal'		=> $hpp,
                'nominal_fc'    => $hpp,
                'note'          => 'Item dikirimkan / keluar dari gudang.',
                'lookable_type'	=> $modp->getTable(),
                'lookable_id'	=> $modp->id,
                'detailable_type'=> $row->getTable(),
                'detailable_id'	=> $row->id,
            ]);

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'coa_id'		=> $row->itemStock->item->itemGroup->coa_id,
                'place_id'		=> $row->itemStock->place_id,
                'item_id'		=> $row->itemStock->item_id,
                'warehouse_id'	=> $row->itemStock->warehouse_id,
                'type'			=> '2',
                'nominal'		=> $hpp,
                'nominal_fc'    => $hpp,
                'note'          => 'Item dikirimkan / keluar dari gudang.',
                'lookable_type'	=> $modp->getTable(),
                'lookable_id'	=> $modp->id,
                'detailable_type'=> $row->getTable(),
                'detailable_id'	=> $row->id,
            ]);

            CustomHelper::sendCogs($modp->getTable(),
                $modp->id,
                $row->itemStock->place->company_id,
                $row->itemStock->place_id,
                $row->itemStock->warehouse_id,
                $row->itemStock->item_id,
                $row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                $hpp,
                'OUT',
                $modp->post_date,
                $row->itemStock->area_id,
                $row->itemStock->item_shading_id,
                $row->itemStock->production_batch_id,
                $row->getTable(),
                $row->id,
            );

            CustomHelper::sendStock(
                $row->itemStock->place_id,
                $row->itemStock->warehouse_id,
                $row->itemStock->item_id,
                $row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                'OUT',
                $row->itemStock->area_id,
                $row->itemStock->item_shading_id,
                $row->itemStock->production_batch_id,
            );
        }
    }

    public function createJournalReceiveDocument(){
        $modp = MarketingOrderDeliveryProcess::find($this->id);
			
        $query = Journal::create([
            'user_id'		=> $modp->user_id,
            'company_id'    => $modp->company_id,
            'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($modp->receive_date)).'00'),
            'lookable_type'	=> 'marketing_order_delivery_processes',
            'lookable_id'	=> $modp->id,
            'post_date'		=> $modp->receive_date,
            'note'			=> $modp->note_internal.' - '.$modp->note_external,
            'status'		=> '3'
        ]);
        
        $coabdp = Coa::where('code','100.01.04.05.01')->where('company_id',$modp->company_id)->first();
        $coahpp = Coa::where('code','500.01.01.01.01')->where('company_id',$modp->company_id)->first();

        foreach($modp->marketingOrderDeliveryProcessDetail as $row){
            $hpp = $row->total;

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'account_id'	=> $coahpp->bp_journal ? $modp->marketingOrderDelivery->customer_id : NULL,
                'coa_id'		=> $coahpp->id,
                'place_id'		=> $row->itemStock->place_id,
                'item_id'		=> $row->itemStock->item_id,
                'warehouse_id'	=> $row->itemStock->warehouse_id,
                'type'			=> '1',
                'nominal'		=> $hpp,
                'nominal_fc'	=> $hpp,
                'note'          => 'Dokumen Surat Jalan telah kembali ke admin penagihan.',
                'lookable_type'	=> $modp->getTable(),
                'lookable_id'	=> $modp->id,
                'detailable_type'=> $row->getTable(),
                'detailable_id'	=> $row->id,
            ]);

            JournalDetail::create([
                'journal_id'	=> $query->id,
                'account_id'	=> $coabdp->bp_journal ? $modp->marketingOrderDelivery->customer_id : NULL,
                'coa_id'		=> $coabdp->id,
                'place_id'		=> $row->itemStock->place_id,
                'item_id'		=> $row->itemStock->item_id,
                'warehouse_id'	=> $row->itemStock->warehouse_id,
                'type'			=> '2',
                'nominal'		=> $hpp,
                'nominal_fc'	=> $hpp,
                'note'          => 'Dokumen Surat Jalan telah kembali ke admin penagihan.',
                'lookable_type'	=> $modp->getTable(),
                'lookable_id'	=> $modp->id,
                'detailable_type'=> $row->getTable(),
                'detailable_id'	=> $row->id,
            ]);
        }
    }

    public function deliveryCost($qty){
        $price = 0;
        $total = 0;
        $typeTransport = $this->marketingOrderDelivery->cost_delivery_type;
        $place = Place::where('code',substr($this->code,7,2))->where('status','1')->first();
        $cityFrom = $place->city_id;
        /* $districtFrom = $place->district_id; */
        $cityTo = $this->marketingOrderDelivery->city_id;
        /* $districtTo = $this->marketingOrderDelivery->district_id; */
        $deliveryCost = DeliveryCost::where('account_id',$this->account_id)->where('valid_from','<=',$this->post_date)->where('valid_to','>=',$this->post_date)->where('transportation_id',$this->marketingOrderDelivery->transportation_id)->where('from_city_id',$cityFrom)->where('to_city_id',$cityTo)->where('status','1')->orderByDesc('id')->first();
        if($deliveryCost){
            if($typeTransport == '1'){
                $price = $deliveryCost->tonnage;
                $total = round($price * $qty,2);
            }elseif($typeTransport == '2'){
                $price = $deliveryCost->ritage;
                $total = round($price,2);
            }
        }
        
        return $total;
    }

    public function hasBalance(){
        $status = true;

        return $status;
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }
    public function isOpenPeriod(){
        $monthYear = substr($this->post_date, 0, 7); // '2023-02'

        // Query the LockPeriod model
        $see = LockPeriod::where('month', $monthYear)
                        ->whereIn('status_closing', ['2','3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }
}
