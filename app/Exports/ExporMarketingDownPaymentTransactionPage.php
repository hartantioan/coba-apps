<?php

namespace App\Exports;
use App\Helpers\CustomHelper;
use App\Models\MarketingOrderDownPayment;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExporMarketingDownPaymentTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status,$type,$account,$company,$currency,$end_date,$start_date,$dataplaces,$dataplacecode;
    
    public function __construct(string $search, string $status, string $type, string $account, string $company, string $currency, string $end_date, string $start_date)
    {

        $user = User::find(Session::get('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->search = $search ? $search : '';
		$this->type = $type ? $type : '';
        $this->account = $account ? $account : '';
		$this->company = $company ? $company : '';
        $this->currency = $currency ? $currency : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->start_date = $start_date ? $start_date : '';
    }

    private $headings = [
        'No',
        'Kode',
        'Post Date',
        'User',
        'BP',
        'Nama NPWP',
        'No. NPWP',
        'Alamat. NPWP',
        'Perusahaan',
        'Tipe',
        'Currency',
        'Note',
        'Pajak',
        'Subtotal',
        'Total',
        'Tax',
        'Grandtotal',
        'Status',
       
    ];
    
    public function collection()
    {
        $query =MarketingOrderDownPayment::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('subtotal', 'like', "%$this->search%")
                        ->orWhere('discount', 'like', "%$this->search%")
                        ->orWhere('total', 'like', "%$this->search%")
                        ->orWhere('tax', 'like', "%$this->search%")
                        ->orWhere('grandtotal', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhere('tax_no', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->whereIn('status', $this->status);
            }

            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }

            if($this->type){
                $query->where('type',$this->type);
            }

            if($this->account){
                $query->whereIn('account_id',$this->account);
            }
            
            if($this->company){
                $query->where('company_id',$this->company);
            }
            
            if($this->currency){
                $query->whereIn('currency_id',$this->currency);
            }
        })
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->get();

        $array = [];
        foreach($query as $index=>$row_arr){
        $user = $row_arr->user->name;
            $account = $row_arr->account->name;
            $array[]=[
                'no' => $index+1,
                'kode' =>$row_arr->code,
                'post_date' =>date('d/m/Y',strtotime($row_arr->post_date)),
                'user' =>$user,
                'bp' =>$account,
                'npwp_name' => $row_arr->account->userDataDefault()->title,
                'npwp_no' => $row_arr->account->userDataDefault()->npwp,
                'npwp_address' => $row_arr->account->userDataDefault()->address,
                'perusahaan' =>$row_arr->plant,
                'tipe' =>$row_arr->type(),
                'currency' =>$row_arr->currency->code,
                'note' =>$row_arr->note,
                'pajak' =>$row_arr->tax_no,
                'subtotal' =>$row_arr->subtotal,
                'total' =>$row_arr->total,
                'tax' =>$row_arr->tax,
                'grandtotal' =>$row_arr->grandtotal,
                'status' =>$row_arr->statusRaw(),
                
            ];
        }
        return collect($array);
    }

    public function title(): string
    {
        return 'Marketing Down Payment';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}
}
