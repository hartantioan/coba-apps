<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\User;
use Illuminate\Http\Request;

class SubsidiaryLedgerController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => '',
            'content'   => 'admin.accounting.subsidiary_ledger',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function process(Request $request){
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $coa_start = $request->coa_start;
        $coa_end = $request->coa_end;

        if($coa_start > $coa_end || $date_start > $date_end){
            return response()->json([
                'message'           => 'Kode Coa Mulai tidak boleh lebih dari Coa Akhir, atau Tanggal Mulai tidak boleh lebih dari Tanggal Akhir.',
                'status'            => 500,
            ]);
        }

        if(!$coa_start || !$coa_end){
            return response()->json([
                'message'           => 'Coa tidak boleh kosong.',
                'status'            => 500,
            ]);
        }

        $start_time = microtime(true);

        #logic here
        
        $html = '<table class="bordered" id="table-result">
                    <thead class="sidebar-sticky" style="background-color:white;">
                        <tr>
                            <th class="center-align" colspan="3">Transaksi</th>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                            <th class="center-align">Saldo</th>
                        </tr></thead><tbody>';

        $coas = Coa::where('status','1')->where('level','5')->whereRaw("code BETWEEN '$coa_start' AND '$coa_end'")->orderBy('code')->get();

        foreach($coas as $key => $row){
            $rowdata = null;
            $rowdata = $row->journalDetail()->whereHas('journal',function($query)use($date_start,$date_end){
                $query->whereRaw("post_date BETWEEN '$date_start' AND '$date_end'");
            })->where('nominal','!=',0)->get();
            if(count($rowdata) > 0){
                $html .= '<tr><td colspan="6" style="font-weight:800;">'.$row->name.'</td></tr>';
                $balance = 0;
                foreach($rowdata as $key => $detail){
                    $balance += ($detail->type == '1' ? $detail->nominal : -1 * $detail->nominal);
                    $html .= '<tr>
                                <td width="100px">'.date('d/m/y',strtotime($detail->journal->post_date)).'</td>
                                <td width="200px">'.$detail->journal->code.'</td>
                                <td>'.$detail->journal->note.' - '.$detail->note.'</td>
                                <td class="right-align">'.($detail->type == '1' ? number_format($detail->nominal,2,',','.') : '-').'</td>
                                <td class="right-align">'.($detail->type == '2' ? number_format($detail->nominal,2,',','.') : '-').'</td>
                                <td class="right-align">'.number_format($balance,2,',','.').'</td>
                            </tr>';
                }
            }
        }

        $html .= '</tbody></table>';
        

        #end logic

        $end_time = microtime(true);

        $execution_time = ($end_time - $start_time);

        $html .= '<div class="center-align">Waktu eksekusi : <b>'.$execution_time.'</b> detik</div>';

        return response()->json([
            'message'           => 'Data berhasil diproses',
            'status'            => 200,
            'html'              => $html,
        ]);
    }
}