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
        'branch_code',
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

    public static function getListCurrentTaxSeries($company_id,$year,$prefix){
        $dataInvoice = MarketingOrderInvoice::whereIn('status',['2','3','5'])->where('company_id',$company_id)->whereRaw("SUBSTRING(tax_no,8,2) = '$year'")->whereNotNull('tax_no')->get();
        $dataDp = MarketingOrderDownPayment::whereIn('status',['2','3','5'])->where('company_id',$company_id)->whereRaw("SUBSTRING(tax_no,8,2) = '$year'")->whereNotNull('tax_no')->get();
        $arr = [];
        foreach($dataInvoice as $row){
            if(!in_array($row->tax_no,$arr)){
                $arr[] = substr($row->tax_no,10,8);
            }
        }
        rsort($arr);
        return $arr;
    } 

    public static function getTaxCode($company_id,$date,$prefix){
        $data = TaxSeries::where('company_id',$company_id)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('status','1')->first();

        if($data){
            $year = date('y',strtotime($date));
            $list = TaxSeries::getListCurrentTaxSeries($company_id,$year,$prefix);
            $no = '';
            if(count($list) > 0){
                $lastData = $list[0];
                $currentno = intval($lastData) + 1;
                $startno = intval($data->start_no);
                $endno = intval($data->end_no);
                if($currentno >= $startno && $currentno <= $endno){
                    $newcurrent = str_pad($currentno, 8, 0, STR_PAD_LEFT);
                    $no = $prefix.'.'.$data->branch_code.'.'.$data->year.'.'.$newcurrent;
                    $response = [
                        'status'    => 200,
                        'no'        => $no,
                    ];
                }else{
                    $response = [
                        'status'  => 500,
                        'message' => 'Nomor seri baru di luar batas seri pajak yang ada. Silahkan tambahkan data terbaru.'
                    ];
                }
            }else{
                $no = $prefix.'.'.$data->branch_code.'.'.$data->year.'.'.$data->start_no;
                $response = [
                    'status'    => 200,
                    'no'        => $no,
                ];
            }
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data Nomor Seri Pajak untuk perusahaan dan tanggal tidak ditemukan. Silahkan atur di Master Data - Akunting - Seri Pajak.'
            ];
        }

        return $response;
    }
}