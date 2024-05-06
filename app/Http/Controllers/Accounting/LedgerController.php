<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLedger;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Buku Besar (Ledger)',
            'content'   => 'admin.accounting.ledger',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'company',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Coa::where('status','1')->where('level','5')->count();
        
        $query_data = Coa::where(function($query) use ($search, $request) {
                    if($search) {
                        $query->where(function($query) use ($search) {
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
                        });
                    }

                    if($request->coa) {
                        $query->where('id', $request->coa);
                    }
                })
                ->where('company_id',$request->company)
                ->where('level','5')
                ->where('status', 1)
                ->offset($start)
                ->limit($length)
                ->orderBy($order, $dir)
                ->get();

        $total_filtered = Coa::where(function($query) use ($search, $request) {
                    if($search) {
                        $query->where(function($query) use ($search) {
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name', 'like', "%$search%");
                        });
                    }     

                    if($request->coa) {
                        $query->where('id', $request->coa);
                    }
                })
                ->where('company_id',$request->company)
                ->where('level','5')
                ->where('status', 1)
                ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {

                $balance_debit = 0;
                $balance_credit = 0;
				
                if($request->start_date && $request->finish_date) {
                    $periode = "DATE(post_date) >= '$request->start_date' AND DATE(post_date) <= '$request->finish_date'";
                } else if($request->start_date) {
                    $periode = "DATE(post_date) >= '$request->start_date' AND DATE(post_date) <= CURDATE()";
                } else if($request->finish_date) {
                    $periode = "DATE(post_date) >= CURDATE() AND DATE(post_date) <= '$request->finish_date'";
                } else {
                    $periode = "";
                }
                $datadebitbefore  = $val->journalDebit()->whereHas('journal',function($query)use($request){
                    $query->whereDate('post_date','<',$request->start_date);
                })->get();
                $datacreditbefore  = $val->journalCredit()->whereHas('journal',function($query)use($request){
                    $query->whereDate('post_date','<',$request->start_date);
                })->get();

                foreach($datadebitbefore as $row){
                    $balance_debit += round($row->nominal,2);
                }

                foreach($datacreditbefore as $row){
                    $balance_credit += round($row->nominal,2);
                }

                $balance = $balance_debit - $balance_credit;
                $total_debit = 0;
                $total_credit = 0;
                $ending_debit = $val->journalDebit()->whereHas('journal',function($query)use($periode,$request){
                    $query->whereRaw($periode)
                        ->where(function($query)use($request){
                            if($request->is_closing_journal){
                                $query->where('lookable_type','!=','closing_journals')
                                    ->orWhereNull('lookable_type');
                            }
                        });
                })->get();
                $ending_credit = $val->journalCredit()->whereHas('journal',function($query)use($periode,$request){
                    $query->whereRaw($periode)
                        ->where(function($query)use($request){
                            if($request->is_closing_journal){
                                $query->where('lookable_type','!=','closing_journals')
                                    ->orWhereNull('lookable_type');
                            }
                        });
                })->get();

                foreach($ending_debit as $rowdebit){
                    $total_debit += round($rowdebit->nominal,2);
                }
    
                foreach($ending_credit as $rowcredit){
                    $total_credit += round($rowcredit->nominal,2);
                }

                $ending_total  = $balance + $total_debit - $total_credit;

                $response['data'][] = [
                    '<button class="btn-floating green btn-small" style="padding: 0 0 !important;" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code.' - '.$val->name,
                    $val->company->name,
                    number_format($balance, 2, ',', '.'),
                    number_format($total_debit, 2, ',', '.'),
                    number_format($total_credit, 2, ',', '.'),
                    number_format($ending_total, 2, ',', '.')
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function rowDetail(Request $request){
        $coa   = Coa::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Jurnal</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Tanggal</th>
                                <th class="center-align">Ket.Header</th>
                                <th class="center-align">Ket.Detail</th>
                                <th class="center-align">Partner Bisnis</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Debit</th>
                                <th class="center-align">Kredit</th>
                                <th class="center-align">Saldo</th>
                            </tr>
                        </thead><tbody>';
        
        $beginning_total = 0;
        $no = 2;

        $balance_debit = 0;
        $balance_credit = 0;

        $datadebitbefore  = $coa->journalDebit()->whereHas('journal',function($query)use($request){
            $query->whereDate('post_date','<',$request->start_date);
        })->get();
        $datacreditbefore  = $coa->journalCredit()->whereHas('journal',function($query)use($request){
            $query->whereDate('post_date','<',$request->start_date);
        })->get();

        foreach($datadebitbefore as $row){
            $balance_debit += round($row->nominal,2);
        }

        foreach($datacreditbefore as $row){
            $balance_credit += round($row->nominal,2);
        }

        $balance = $balance_debit - $balance_credit;

        $string .= '<tr>
            <td class="center-align" colspan="14"><b>SALDO PERIODE SEBELUMNYA</b></td>
            <td class="right-align blue-text text-darken-2"><b>'.number_format($balance,2,',','.').'</b></td>
        </tr>';

        $beginning_total = $balance;

        foreach(JournalDetail::where('coa_id',$coa->id)->whereHas('journal',function($query)use($request){
            if($request->start_date && $request->finish_date) {
                $query->whereDate('post_date', '>=', $request->start_date)
                    ->whereDate('post_date', '<=', $request->finish_date);
            } else if($request->start_date) {
                $query->whereDate('post_date', '>=', $request->start_date)
                    ->whereDate('post_date', '<=', date('Y-m-d'));
            } else if($request->finish_date) {
                $query->whereDate('post_date', '>=', date('Y-m-d'))
                    ->whereDate('post_date', '<=', $request->finish_date);
            }
            $query->where(function($query)use($request){
                if($request->is_closing_journal){
                    $query->where('lookable_type','!=','closing_journals')
                        ->orWhereNull('lookable_type');
                }
            });
        })->get()->sortBy(function($query){
           $query->journal->post_date;
        }) as $key => $row){
            if($row->type == '1'){
                $beginning_total += $row->nominal;
            }elseif($row->type == '2'){
                $beginning_total -= $row->nominal;
            }
            $string .= '<tr>
                <td class="center-align">'.$no.'</td>
                <td>'.$row->journal->code.'</td>
                <td>'.$row->coa->name.'</td>
                <td class="center-align">'.date('d/m/Y',strtotime($row->journal->post_date)).'</td>
                <td>'.$row->journal->note.'</td>
                <td>'.$row->note.'</td>
                <td>'.($row->account_id ? $row->account->name : '-').'</td>
                <td>'.($row->place_id ? $row->place->code : '-').'</td>
                <td>'.($row->line_id ? $row->line->name : '-').'</td>
                <td>'.($row->machine_id ? $row->machine->name : '-').'</td>
                <td>'.($row->department_id ? $row->department->name : '-').'</td>
                <td>'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '-').'</td>
                <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '-').'</td>
                <td class="right-align blue-text text-darken-2"><b>'.number_format($beginning_total,2,',','.').'</b></td>
            </tr>';
            $no++;
        }
        
        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : date('Y-m-d');
        $end_date = $request->end_date ? $request->end_date : date('Y-m-d');
        $coa_id = $request->coa_id ? $request->coa_id : '';
        $company_id = $request->company_id ? $request->company_id : '';
        $search = $request->search ? $request->search : '';
        $closing_journal = $request->closing_journal ? $request->closing_journal : '';

		return Excel::download(new ExportLedger($start_date,$end_date,$coa_id,$company_id,$search,$closing_journal), 'ledger_'.uniqid().'.xlsx');
    }
}
