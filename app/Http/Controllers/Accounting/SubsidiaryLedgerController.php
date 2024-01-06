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
        
        $html = '<table class="bordered" id="table-result" style="min-width:2500px !important;zoom:0.6;">
                    <thead class="sidebar-sticky" style="background-color:white;">
                        <tr>
                            <th class="center-align" width="150px">Kode Coa</th>
                            <th class="center-align" width="500px">Nama Coa</th>
                            <th class="center-align" width="100px">Tanggal</th>
                            <th class="center-align" width="200px">No.JE</th>
                            <th class="center-align" width="200px">Dok.Ref.</th>
                            <th class="center-align">Debit</th>
                            <th class="center-align">Kredit</th>
                            <th class="center-align">Total</th>
                            <th class="center-align">Keterangan 1</th>
                            <th class="center-align">Keterangan 2</th>
                            <th class="center-align">Plant</th>
                            <th class="center-align">Gudang</th>
                            <th class="center-align">Line</th>
                            <th class="center-align">Mesin</th>
                            <th class="center-align">Departemen</th>
                            <th class="center-align">Proyek</th>
                        </tr></thead><tbody>';

        $coas = Coa::where('status','1')->where('level','5')->whereRaw("code BETWEEN '$coa_start' AND '$coa_end'")->orderBy('code')->get();

        foreach($coas as $key => $row){
            $rowdata = null;
            $rowdata = $row->journalDetail()->whereHas('journal',function($query)use($date_start,$date_end){
                $query->whereRaw("post_date BETWEEN '$date_start' AND '$date_end'");
            })->where('nominal','!=',0)->get();
            $balance = $row->getBalanceFromDate($date_start);
            $html .= '<tr style="font-weight:800;">
                            <td width="200px">'.$row->code.'</td>
                            <td width="200px">'.$row->name.'</td>
                            <td colspan="5"></td>
                            <td class="right-align">'.number_format($balance,2,',','.').'</td>
                            <td colspan="8"></td>
                        </tr>';
            if(count($rowdata) > 0){
                foreach($rowdata as $key => $detail){
                    $balance += ($detail->type == '1' ? $detail->nominal : -1 * $detail->nominal);
                    $html .= '<tr>
                                <td>'.$detail->coa->code.'</td>
                                <td>'.$detail->coa->name.'</td>
                                <td>'.date('d/m/y',strtotime($detail->journal->post_date)).'</td>
                                <td>'.$detail->journal->code.'</td>
                                <td>'.($detail->journal->lookable_id ? $detail->journal->lookable->code : '-').'</td>
                                <td class="right-align">'.($detail->type == '1' ? number_format($detail->nominal,2,',','.') : '-').'</td>
                                <td class="right-align">'.($detail->type == '2' ? number_format($detail->nominal,2,',','.') : '-').'</td>
                                <td class="right-align">'.number_format($balance,2,',','.').'</td>
                                <td>'.$detail->journal->note.'</td>
                                <td>'.$detail->note.'</td>
                                <td>'.($detail->place()->exists() ? $detail->place->code : '-').'</td>
                                <td>'.($detail->warehouse()->exists() ? $detail->warehouse->name : '-').'</td>
                                <td>'.($detail->line()->exists() ? $detail->line->code : '-').'</td>
                                <td>'.($detail->machine()->exists() ? $detail->machine->code : '-').'</td>
                                <td>'.($detail->department()->exists() ? $detail->department->code : '-').'</td>
                                <td>'.($detail->project()->exists() ? $detail->project->code : '-').'</td>
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