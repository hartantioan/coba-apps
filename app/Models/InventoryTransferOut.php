<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InventoryTransferOut extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventory_transfer_outs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'place_from',
        'warehouse_from',
        'place_to',
        'warehouse_to',
        'post_date',
        'document',
        'note',
        'receiver_id',
        'received_date',
        'document_received',
        'note_received',
        'status',
        'void_id',
        'void_note',
        'void_date',
    ];

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function placeFrom(){
        return $this->belongsTo('App\Models\Place','place_from','id')->withTrashed();
    }

    public function warehouseFrom(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_from','id')->withTrashed();
    }

    public function placeTo(){
        return $this->belongsTo('App\Models\Place','place_to','id')->withTrashed();
    }

    public function warehouseTo(){
        return $this->belongsTo('App\Models\Warehouse','warehouse_to','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function receiver(){
        return $this->belongsTo('App\Models\User','receiver_id','id')->withTrashed();
    }

    public function voidUser(){
        return $this->belongsTo('App\Models\User','void_id','id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function inventoryTransferOutDetail()
    {
        return $this->hasMany('App\Models\InventoryTransferOutDetail');
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

    public function attachment() 
    {
        if($this->document !== NULL && Storage::exists($this->document)) {
            $document = asset(Storage::url($this->document));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }

    public function deleteFile(){
		if(Storage::exists($this->document)) {
            Storage::delete($this->document);
        }
	}

    public static function generateCode()
    {
        $query = InventoryTransferOut::selectRaw('RIGHT(code, 9) as code')
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

        $pre = 'ITO-'.date('y').date('m').date('d').'-';

        return $pre.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->first();
        if($source && $source->approvalMatrix()->exists()){
            return $source;
        }else{
            return '';
        }
    }

    public function listApproval(){
        $source = $this->approval();
        if($source){
            $html = '';
            foreach($source->approvalMatrix()->whereHas('approvalTemplateStage',function($query){ $query->whereHas('approvalStage', function($query) { $query->orderBy('level'); }); })->get() as $row){
                $html .= '<span style="top:-10px;">'.$row->user->name.'</span> '.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br>';
            }

            return $html;
        }else{
            return '';
        }
    }

    public function updateJournal(){
        $journal = Journal::where('lookable_type',$this->table)->where('lookable_id',$this->id)->first();
        
        if($journal){
            foreach($this->inventoryTransferOutDetail as $row){
                $priceout = $row->item->priceNow($row->itemStock->place_id,$this->post_date);
				$nominal = round($row->qty * $priceout,2);

                $row->update([
                    'price'     => $priceout,
                    'total'     => $nominal
                ]);

                if($journal){
                    foreach($journal->journalDetail()->where('item_id',$row->item_id)->get() as $rowupdate){
                        $rowupdate->update([
                            'nominal'   => $nominal
                        ]);
                    }
                }
            }
        }
    }

    public function journal(){
        return $this->hasOne('App\Models\Journal','lookable_id','id')->where('lookable_type',$this->table);
    }
}
