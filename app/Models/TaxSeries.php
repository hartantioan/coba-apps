<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TaxSeries extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'tax_series';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'company_id',
        'npwp',
        'djp_letter_no',
        'pkp_letter_no',
        'year',
        'document',
        'start_date',
        'end_date',
        'start_no',
        'end_no',
        'note',
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id')->withTrashed();
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

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

    public static function getListCurrentTaxSeries($company_id,$year){
        $dataInvoice = MarketingOrderInvoice::whereIn('status',['2','3'])->where('company_id',$company_id)->whereRaw("SUBSTRING(tax_no,5,2) = '$year'")->whereNotNull('tax_no')->pluck('tax_no')->toArray();
        $dataDp = MarketingOrderDownPayment::whereIn('status',['2','3'])->where('company_id',$company_id)->whereRaw("SUBSTRING(tax_no,5,2) = '$year'")->whereNotNull('tax_no')->pluck('tax_no')->toArray();
        $newList = array_merge($dataInvoice,$dataDp);
        rsort($newList);
        return $newList;
    }
}