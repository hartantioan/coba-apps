<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Monolog\Formatter\FormatterInterface;

class GoodScale extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_scales';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'place_id',
        'post_date',
        'delivery_no',
        'vehicle_no',
        'driver',
        'document',
        'image_in',
        'image_out',
        'note',
        'status',
        'void_id',
        'void_note',
        'void_date',
    ];

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function goodScaleDetail()
    {
        return $this->hasMany('App\Models\GoodScaleDetail');
    }

    public static function generateCode($post_date)
    {
        $query = GoodScale::selectRaw('RIGHT(code, 9) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000000001';
        }

        $no = str_pad($code, 9, 0, STR_PAD_LEFT);

        $pre = 'GS-'.date('ymd',strtotime($post_date)).'-';

        return $pre.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
        }
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

    public function attachment() 
    {
        if($this->document !== NULL && Storage::exists($this->document)) {
            $document = asset(Storage::url($this->document));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }

    public function imageIn() 
    {
        if($this->image_in !== NULL && Storage::exists($this->image_in)) {
            $image = asset(Storage::url($this->image_in));
        } else {
            $image = asset('website/empty.png');
        }

        return $image;
    }

    public function imageOut() 
    {
        if($this->image_out !== NULL && Storage::exists($this->image_out)) {
            $image = asset(Storage::url($this->image_out));
        } else {
            $image = asset('website/empty.png');
        }

        return $image;
    }

    public function deleteFile(){
		if(Storage::exists($this->document)) {
            Storage::delete($this->document);
        }
	}

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
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

    public function hasChildDocument(){
        $hasRelation = false;

        return $hasRelation;
    }

    public function referencePO(){
        $arr = [];

        foreach($this->goodScaleDetail as $row){
            if($row->purchase_order_detail_id){
                if(!in_array($row->purchaseOrderDetail->purchaseOrder->code,$arr)){
                    $arr[] = $row->purchaseOrderDetail->purchaseOrder->code;
                }
            }
        }
        if(count($arr) > 0){
            return implode(', ',$arr);
        }else{
            return '-';
        }
    }
    
    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function createGoodReceipt(){

        DB::beginTransaction();
        try {
            $totalall = 0;
            $taxall = 0;
            $wtaxall = 0;
            $grandtotalall = 0;
            $arrDetail = [];

            foreach($this->goodScaleDetail as $key => $row){
                $wtax = 0;
                $total = 0;
                $grandtotal = 0;
                $tax = 0;

                $discount = $row->purchaseOrderDetail->purchaseOrder->discount;
                $subtotal = $row->purchaseOrderDetail->purchaseOrder->subtotal;

                $rowprice = 0;

                $bobot = $row->purchaseOrderDetail->subtotal / $subtotal;
                $rowprice = round($row->purchaseOrderDetail->subtotal / $row->purchaseOrderDetail->qty,2);

                $total = ($rowprice * $row->qty_balance) - ($bobot * $discount);

                if($row->purchaseOrderDetail->is_tax == '1' && $row->purchaseOrderDetail->is_include_tax == '1'){
                    $total = $total / (1 + ($row->purchaseOrderDetail->percent_tax / 100));
                }

                if($row->purchaseOrderDetail->is_tax == '1'){
                    $tax = round($total * ($row->purchaseOrderDetail->percent_tax / 100),2);
                }

                if($row->purchaseOrderDetail->is_wtax == '1'){
                    $wtax = round($total * ($row->purchaseOrderDetail->percent_wtax / 100),2);
                }

                $grandtotal = $total + $tax - $wtax;

                $arrDetail[] = [
                    'total'         => $total,
                    'tax'           => $tax,
                    'wtax'          => $wtax,
                    'grandtotal'    => $grandtotal,
                ];

                $totalall += $total;
                $taxall += $tax;
                $wtaxall += $wtax;
                $grandtotalall += $grandtotal;
            }

            $newFile = '';
            if($this->document){
                $name = Str::random(40).'.'.explode('.',explode('/',$this->document)[2])[1];
                $newFile = 'public/good_receipts/'.$name;
                $fileFrom = 'public/good_scales/'.explode('/',$this->document)[2];
                $fileTo = 'public/good_receipts/'.$name;
                Storage::copy($fileFrom,$fileTo);
            }
            
            $query = GoodReceipt::create([
                'code'			        => GoodReceipt::generateCode($this->post_date),
                'user_id'		        => session('bo_id'),
                'account_id'            => $this->account_id,
                'company_id'            => $this->company_id,
                'receiver_name'         => $this->receiver_name,
                'post_date'             => $this->post_date,
                'due_date'              => $this->post_date,
                'document_date'         => $this->post_date,
                'delivery_no'           => $this->delivery_no,
                'document'              => $newFile ? $newFile : NULL,
                'note'                  => $this->note,
                'status'                => '1',
                'total'                 => $totalall,
                'tax'                   => $taxall,
                'wtax'                  => $wtaxall,
                'grandtotal'            => $grandtotalall
            ]);

            if($query){
                foreach($this->goodScaleDetail as $key => $row){
                    GoodReceiptDetail::create([
                        'good_receipt_id'           => $query->id,
                        'purchase_order_detail_id'  => $row->purchase_order_detail_id,
                        'good_scale_detail_id'      => $row->id,
                        'item_id'                   => $row->item_id,
                        'qty'                       => $row->qty_balance,
                        'total'                     => $arrDetail[$key]['total'],
                        'tax'                       => $arrDetail[$key]['tax'],
                        'wtax'                      => $arrDetail[$key]['wtax'],
                        'grandtotal'                => $arrDetail[$key]['grandtotal'],
                        'note'                      => $row->note,
                        'note2'                     => $row->note2,
                        'remark'                    => '-',
                        'place_id'                  => $row->place_id,
                        'warehouse_id'              => $row->warehouse_id,
                    ]);
                }

                CustomHelper::sendApproval('good_receipts',$query->id,$query->note);
                CustomHelper::sendNotification('good_receipts',$query->id,'Pengajuan Penerimaan Barang No. '.$query->code,$query->note,session('bo_id'));
                activity()
                    ->performedOn(new GoodReceipt())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit penerimaan barang.');
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }
    }

    public function alreadyHome(){
        $status = false;
        foreach($this->goodScaleDetail as $row){
            if($row->qty_out > 0){
                $status = true;
            }
        }

        return $status;
    }
}
