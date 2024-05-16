<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportSubsidiaryLedger;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
        $is_closing_journal = $request->is_closing_journal;

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
        
        $html = '<table class="bordered" id="table-result" style="min-width:2500px !important;zoom:0.6;">
                    <thead class="sidebar-sticky" style="background-color:white;">
                        <tr>
                            <th class="center-align" width="150px">Kode Coa</th>
                            <th class="center-align" width="500px">Nama Coa</th>
                            <th class="center-align" width="100px">Tanggal</th>
                            <th class="center-align" width="200px">No.JE</th>
                            <th class="center-align" width="200px">Dok.Ref.</th>
                            <th class="center-align">Debit FC</th>
                            <th class="center-align">Kredit FC</th>
                            <th class="center-align">Debit Rp</th>
                            <th class="center-align">Kredit Rp</th>
                            <th class="center-align">Total Rp</th>
                            <th class="center-align">Keterangan 1</th>
                            <th class="center-align">Keterangan 2</th>
                            <th class="center-align">Keterangan 3</th>
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
            $rowdata = $row->journalDetail()->whereHas('journal',function($query)use($date_start,$date_end,$is_closing_journal){
                $query->whereRaw("post_date BETWEEN '$date_start' AND '$date_end'")
                    ->where(function($query)use($is_closing_journal){
                        if($is_closing_journal){
                            $query->where('lookable_type','!=','closing_journals')
                                ->orWhereNull('lookable_type');
                        }
                    });
            })->where('nominal','!=',0)->get();
            $arrData = [];
            foreach($rowdata as $rowdetail){
                $arrData[] = [
                    'post_date' => $rowdetail->journal->post_date,
                    'data'      => $rowdetail,
                ];
            }
            $collect = collect($arrData)->sortBy('post_date');
            $balance = $row->getBalanceFromDate($date_start);
            $html .= '<tr style="font-weight:800;">
                        <td width="200px">' . $row->code . '</td>
                        <td width="200px">' . $row->name . '</td>
                        <td colspan="7"></td>
                        <td class="right-align">' . ($balance != 0 ? number_format($balance, 2, ',', '.') : '-') . '</td>
                        <td colspan="11"></td>
                    </tr>';
        
            if(count($collect) > 0){
                foreach($collect as $key => $detail){
                    $additional_ref = '';
                    if($detail['data']->journal->lookable_type == 'outgoing_payments'){
                        $additional_ref = ($detail['data']->note ? ' - ' : '').$detail['data']->journal->lookable->paymentRequest->code;
                    }
                    $balance += ($detail['data']->type == '1' ? round($detail['data']->nominal,2) : round(-1 * $detail['data']->nominal,2));
                    $currencySymbol = $detail['data']->journal->currency()->exists() ? $detail['data']->journal->currency->symbol : '';
                    $nominalCurrency = $detail['data']->journal->currency()->exists() ? ($detail['data']->journal->currency->type == '1' ? '' : '1') : '';
                    $html .= '<tr>
                        <td>' . $detail['data']->coa->code . '</td>
                        <td>' . $detail['data']->coa->name . '</td>
                        <td>' . date('d/m/Y', strtotime($detail['data']->journal->post_date)) . '</td>
                        <td>' . $detail['data']->journal->code . '</td>
                        <td>' . ($detail['data']->journal->lookable_id ? $detail['data']->journal->lookable->code : '-') . '</td>
                        <td class="right-align">' . ($detail['data']->type == '1' && $detail['data']->nominal_fc != 0 ? ($nominalCurrency ? $currencySymbol . number_format($detail['data']->nominal_fc, 2, ',', '.') : '-') : '-') . '</td>
                        <td class="right-align">' . ($detail['data']->type == '2' && $detail['data']->nominal_fc != 0 ? ($nominalCurrency ? $currencySymbol . number_format($detail['data']->nominal_fc, 2, ',', '.') : '-') : '-') . '</td>
                        <td class="right-align">' . ($detail['data']->type == '1' && $detail['data']->nominal != 0 ? number_format($detail['data']->nominal, 2, ',', '.') : '-') . '</td>
                        <td class="right-align">' . ($detail['data']->type == '2' && $detail['data']->nominal != 0 ? number_format($detail['data']->nominal, 2, ',', '.') : '-') . '</td>
                        <td class="right-align">' . ($balance != 0 ? number_format($balance, 2, ',', '.') : '-') . '</td>
                        <td>' . $detail['data']->journal->note . '</td>
                        <td>' . $detail['data']->note . $additional_ref . '</td>
                        <td>' . $detail['data']->note2 . '</td>
                        <td>' . ($detail['data']->place()->exists() ? $detail['data']->place->code : '-') . '</td>
                        <td>' . ($detail['data']->warehouse()->exists() ? $detail['data']->warehouse->name : '-') . '</td>
                        <td>' . ($detail['data']->line()->exists() ? $detail['data']->line->code : '-') . '</td>
                        <td>' . ($detail['data']->machine()->exists() ? $detail['data']->machine->code : '-') . '</td>
                        <td>' . ($detail['data']->department()->exists() ? $detail['data']->department->name : '-') . '</td>
                        <td>' . ($detail['data']->project()->exists() ? $detail['data']->project->code : '-') . '</td>
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

    public function export(Request $request){
        $datestart = $request->datestart ? $request->datestart : date('Y-m-d');
        $dateend = $request->dateend ? $request->dateend : date('Y-m-d');
        $coastart = $request->coastart ? $request->coastart : '';
        $coaend = $request->coaend ? $request->coaend : '';
        $closing_journal = $request->closing_journal ? $request->closing_journal : '';

		return Excel::download(new ExportSubsidiaryLedger($datestart,$dateend,$coastart,$coaend,$closing_journal), 'subsidiary_ledger_'.uniqid().'.xlsx');
    }
}