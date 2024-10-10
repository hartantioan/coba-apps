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
        $dataInvoice = MarketingOrderInvoice::whereIn('status',['2','3','5'])->whereNotNull('tax_no')->where('tax_no','!=','')->where('company_id',$company_id)->whereRaw("tax_no <> '' AND SUBSTRING(tax_no,9,2) = '$year'")->get();
        $dataDp = MarketingOrderDownPayment::whereIn('status',['2','3','5'])->whereNotNull('tax_no')->where('tax_no','!=','')->where('company_id',$company_id)->whereRaw("tax_no <> '' AND SUBSTRING(tax_no,9,2) = '$year'")->get();
        $arr = [];
        foreach($dataInvoice as $row){
            if($row->tax_no && !in_array(substr($row->tax_no,11,8),$arr)){
                $arr[] = substr($row->tax_no,11,8);
            }
        }
        foreach($dataDp as $row){
            if($row->tax_no && !in_array(substr($row->tax_no,11,8),$arr)){
                $arr[] = substr($row->tax_no,11,8);
            }
        }
        rsort($arr);
        return $arr;
    } 

    public static function getTaxCode($company_id,$date,$prefix){
        $data = TaxSeries::where('company_id',$company_id)->where('start_date','<=',$date)->where('end_date','>=',$date)->where('status','1')->get();

        if(count($data) > 0){
            $year = date('y',strtotime($date));
            $list = TaxSeries::getListCurrentTaxSeries($company_id,$year,$prefix);
            $no = '';
            $tempNo = '';
            if(count($list) > 0){
                $lastData = $list[0];
                $currentno = intval($lastData) + 1;
                $arrNo = [];
                foreach($data as $row){
                    for($x=$row->start_no;$x<=$row->end_no;$x){
                        $arrNo[] = $x;
                    }
                }
                if(in_array($currentno,$arrNo)){
                    $key = array_search($currentno, $arrNo);
                    $tempNo = $arrNo[$key];
                }else{
                    $key = array_search($lastData, $arrNo);
                    if($key){
                        if($arrNo[$key + 1]){
                            $tempNo = $arrNo[$key + 1];
                        }
                    }
                }
                if($no){
                    $newcurrent = str_pad($tempNo, 8, 0, STR_PAD_LEFT);
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