<?php

namespace App\Exports;
use App\Helpers\CustomHelper;
use App\Models\ApprovalCreditLimit;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExporApprovalCreditLimitTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status,$account,$company,$end_date,$start_date,$dataplacecode;
    
    public function __construct(string $search, string $status, string $account, string $company, string $end_date, string $start_date)
    {

        $user = User::find(Session::get('bo_id'));

        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->search = $search ? $search : '';
        $this->account = $account ? $account : '';
		$this->company = $company ? $company : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->start_date = $start_date ? $start_date : '';
    }

    private $headings = [
        'No',
        'Kode',
        'Status',
        'User',
        'Perusahaan',
        'BP',
        'Brand',
        'Post Date',
        'Note',
        'Credit Limit Sekarang',
        'Credit Limit Baru',
        'Nominal Perubahan',
    ];
    
    public function collection()
    {
        $query = ApprovalCreditLimit::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('account',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }

            if($this->status){
                $arr = explode(',',string: $this->status);
                $query->whereIn('status', $arr);
            }

            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }

            if($this->account){
                $arr = explode(',',$this->account);
                $query->whereIn('account_id',$arr);
            }
            
            if($this->company){
                $query->where('company_id',$this->company);
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
                'status' =>$row_arr->statusRaw(),
                'user' =>$user,
                'perusahaan' =>$row_arr->company->name,
                'bp' =>$account,
                'brand' =>$row_arr->account->brand()->exists() ? $row_arr->account->brand->name : '-',
                'post_date' =>date('d/m/Y',strtotime($row_arr->post_date)),
                'note' =>$row_arr->note,
                'current_credit_limit' =>$row_arr->current_credit_limit,
                'new_credit_limit' =>$row_arr->new_credit_limit,
                'grandtotal' =>$row_arr->grandtotal,
            ];
        }
        return collect($array);
    }

    public function title(): string
    {
        return 'Approval Credit Limit';
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
