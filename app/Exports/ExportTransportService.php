<?php

namespace App\Exports;

use App\Models\GoodReceiptDetail;
use App\Models\LandedCostDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransportService implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date, $item_id;
    public function __construct(string $start_date,string $finish_date,string $item_id)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->item_id = $item_id ? $item_id : '';
    }

    private $headings = [
    ];



    public function collection()
    {

        $query_data = LandedCostDetail::whereHas('landedCost', function($query) {
            $query->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->finish_date)
            ->whereIn('status',["2","3"]);
        })->where('lookable_type', 'good_receipt_details')
        ->whereHas('lookable', function($query) {
            $query->where('item_id', '>=',$this->item_id);
        })
        ->get();
        $grouped_data = $query_data->groupBy(function($item) {
            return $item->landedCost->supplier_id;
        });

        $limited_data = $grouped_data->map(function ($group) {
            return $group;
        });
        foreach ($limited_data as $k=>$row) {
            $no=1;
            $account = '';
            $all_netto = 0;
            $all_biaya_eks = 0;
            foreach($row as $lc_detail){
                if($account == ''){
                    $account = $lc_detail->landedCost->supplier->name;
                    $arr[] = [
                        'NO'=>$account,
                        'PLANT'=>'',
                        'NO LC'=>'',
                        'NO GRPO'=>'',
                        'NAMA VENDOR'=>'',
                        'NO SJ'=>'',
                        'TGL MASUK'=>'',
                        'NETTO SPS'=>'',
                        'BIAYA EKSPEDISI'=>'',
                        'HARGA OP/BBM'=>'',
                    ];
                    $arr[] = [
                        'NO'=>'NO',
                        'PLANT'=>'PLANT',
                        'NO LC'=>'NO LC',
                        'NO GRPO'=>'NO GRPO',
                        'NAMA VENDOR'=>'NAMA VENDOR',
                        'NO SJ'=>'NO SJ',
                        'TGL MASUK'=>'TGL MASUK',
                        'NETTO SPS'=>'NETTO SPS',
                        'BIAYA EKSPEDISI'=>'BIAYA EKSPEDISI',
                        'HARGA OP/BBM'=>'HARGA OP/BBM',
                    ];
                }
                $all_netto += $lc_detail->lookable->qty;
                $all_biaya_eks += $lc_detail->lookable->nominal;

                $arr[] = [
                    'NO'=>$no,
                    'PLANT'=>$lc_detail->place->code,
                    'NO LC'=>$lc_detail->landedCost->code,
                    'NO GRPO'=>$lc_detail->lookable->goodReceipt->code,
                    'NAMA VENDOR'=>$lc_detail->landedCost->account->name,
                    'NO SJ'=>$lc_detail->lookable->goodReceipt->delivery_no,
                    'TGL MASUK'=>date('d/m/Y',strtotime($lc_detail->landedCost->post_date)),
                    'NETTO SPS'=>$lc_detail->lookable->qty,
                    'BIAYA EKSPEDISI'=>$lc_detail->lookable->nominal,
                    'HARGA OP/BBM'=>0,
                ];
                $no++;
            }
            $avg = $all_biaya_eks / (($all_netto != 0) ? $all_netto : 1);
            $all_hargaop = 0;
            foreach ($arr as &$row_arr) {
                if ((is_numeric($row_arr['NETTO SPS']) && $row_arr['NETTO SPS'] > 0) && ($row_arr['NETTO SPS'] == (float)$row_arr['NETTO SPS'] || $row_arr['NETTO SPS'] == (int)$row_arr['NETTO SPS'])) {

                    $mbeng = $row_arr['NETTO SPS'] * $avg;
                    $row_arr['HARGA OP/BBM'] = number_format($mbeng,2,',','.');
                    $all_hargaop += $mbeng;
                }
                // $row_arr['TOTAL PENERIMAAN'] = number_format($row_arr['TOTAL PENERIMAAN'],2,',','.');
            }
            $arr[] = [
                'NO'=>'',
                'PLANT'=>'',
                'NO LC'=>'',
                'NO GRPO'=>'',
                'NAMA VENDOR'=>'',
                'NO SJ'=>'',
                'TGL MASUK'=>'total',
                'NETTO SPS'=>$all_netto,
                'BIAYA EKSPEDISI'=>$all_biaya_eks,
                'HARGA OP/BBM'=>$all_hargaop,
            ];

            $arr[] = [
                'NO'=>'',
                'PLANT'=>'',
                'NO LC'=>'',
                'NO GRPO'=>'',
                'NAMA VENDOR'=>'',
                'NO SJ'=>'',
                'TGL MASUK'=>'',
                'NETTO SPS'=>'avg(rp/satuan)',
                'BIAYA EKSPEDISI'=>$avg,
                'HARGA OP/BBM'=>'',
            ];

            $arr[] = [
                'NO'=>'',
                'PLANT'=>'',
                'NO LC'=>'',
                'NO GRPO'=>'',
                'NAMA VENDOR'=>'',
                'NO SJ'=>'',
                'TGL MASUK'=>'',
                'NETTO SPS'=>'',
                'BIAYA EKSPEDISI'=>session('bo_id'),
                'HARGA OP/BBM'=>'',
            ];



        }

        return collect($arr);


    }


    public function title(): string
    {
        return 'Report Jasa Angkut';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
