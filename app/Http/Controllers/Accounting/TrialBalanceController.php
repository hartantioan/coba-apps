<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\User;
use Illuminate\Http\Request;

class TrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Neraca Saldo (Trial Balance)',
            'content'   => 'admin.accounting.trial_balance',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function process(Request $request){
        $level = $request->level;
        $company_id = $request->company_id;
        $month_start = $request->month_start;
        $month_end = $request->month_end;

        $start_time = microtime(true);

        #logic here
        $arrMonth = [];
        while (strtotime($month_start) <= strtotime($month_end)) {
            $arrMonth[] = [
                'month'                 => date("F'y", strtotime($month_start)),
                'raw_month'             => date("Y-m", strtotime($month_start)),
                'totalBalanceBefore'    => 0,
                'totalDebit'            => 0,
                'totalCredit'           => 0,
                'totalBalance'          => 0,
                'tempBalanceBefore'     => 0,
                'tempDebit'             => 0,
                'tempCredit'            => 0,
                'tempBalance'           => 0,
            ];
            $month_start = date("Y-m", strtotime("+1 month", strtotime($month_start)));
        }

        $html = '<table class="bordered">
                    <thead class="sidebar-sticky" style="background-color:white;">
                        <tr>
                            <th rowspan="2" style="min-width:350px !important;left: 0px;position: sticky;background-color:white;">Nama Coa</th>';

        foreach($arrMonth as $key => $row) {
            $html .= '<th style="min-width:450px !important;" class="center-align" colspan="4">'.$row['month'].'</th>';
        }

        $html .= '</tr><tr>';

        foreach($arrMonth as $key => $row) {
            $html .= '
                <th style="min-width:150px !important;" class="center-align">Saldo Awal</th>
                <th style="min-width:150px !important;" class="center-align">Debit</th>
                <th style="min-width:150px !important;" class="center-align">Kredit</th>
                <th style="min-width:150px !important;" class="center-align">Saldo Akhir</th>';
        }

        $html .= '</tr></thead><tbody>';

        $coas = Coa::where('status','1')->where('company_id',$company_id)->where('level',$level)->whereRaw("SUBSTRING(code,1,1) IN ('1','2','3')")->orderBy('code')->get();

        if($level == '1'){
            foreach($coas as $key => $row){
                $html .= '<tr>
                    <td style="left: 0px;position: sticky;background-color:white;">'.$row->name.'</td>';

                foreach($arrMonth as $key => $rowMonth) {
                    $val = $row->getTotalMonthFromParent($rowMonth['raw_month'],$level);
                    $html .= '
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalDebit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalCredit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'] + $val['totalBalance'],2,',','.').'</td>';
                    $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
                    $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                }

                $html .= '</tr>';
            }
        }elseif($level == '2'){
            $tempParent = 0;
            foreach($coas as $keymain => $row){
                if($tempParent !== $row->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>'.$row->parentSub->name.'</b></td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                    foreach($arrMonth as $key => $rowMonth) {
                        $arrMonth[$key]['tempBalanceBefore'] = 0;
                        $arrMonth[$key]['tempDebit'] = 0;
                        $arrMonth[$key]['tempCredit'] = 0;
                        $arrMonth[$key]['tempBalance'] = 0;
                    }
                }
                $html .= '<tr>
                    <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;'.$row->name.'</td>';

                foreach($arrMonth as $key => $rowMonth) {
                    $val = $row->getTotalMonthFromParent($rowMonth['raw_month'],$level);
                    $html .= '
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalDebit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalCredit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'] + $val['totalBalance'],2,',','.').'</td>';
                    $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                    $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
                }

                $html .= '</tr>';

                if(isset($coas[$keymain + 1])){
                    if($coas[$keymain + 1]->parent_id !== $row->parent_id){
                        $html .= '<tr>
                            <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->name.'</b></td>';
                        
                        foreach($arrMonth as $key => $rowMonth) {
                            $html .= '
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                        }
                        
                        $html .= '</tr>';
                    }
                }else{
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->name.'</b></td>';

                    foreach($arrMonth as $key => $rowMonth) {
                        $html .= '
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                    }

                    $html .= '</tr>';
                }

                $tempParent = $row->parent_id;
            }
        }elseif($level == '3'){
            $tempParent1 = 0;
            $tempParent2 = 0;
            foreach($coas as $keymain => $row){
                if($tempParent2 !== $row->parentSub->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>'.$row->parentSub->parentSub->name.'</b></td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                    foreach($arrMonth as $key => $rowMonth) {
                        $arrMonth[$key]['tempBalanceBefore'] = 0;
                        $arrMonth[$key]['tempDebit'] = 0;
                        $arrMonth[$key]['tempCredit'] = 0;
                        $arrMonth[$key]['tempBalance'] = 0;
                    }
                }
                if($tempParent1 !== $row->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;'.$row->parentSub->name.'</td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                }
                $html .= '<tr>
                    <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row->name.'</td>';

                foreach($arrMonth as $key => $rowMonth) {
                    $val = $row->getTotalMonthFromParent($rowMonth['raw_month'],$level);
                    $html .= '
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalDebit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalCredit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalance'],2,',','.').'</td>';
                    $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                    $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
                }

                $html .= '</tr>';

                if(isset($coas[$keymain + 1])){
                    if($coas[$keymain + 1]->parentSub->parent_id !== $row->parentSub->parent_id){
                        $html .= '<tr>
                            <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->parentSub->name.'</b></td>';
                        
                        foreach($arrMonth as $key => $rowMonth) {
                            $html .= '
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                        }
                        
                        $html .= '</tr>';
                    }
                }else{
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->parentSub->name.'</b></td>';

                    foreach($arrMonth as $key => $rowMonth) {
                        $html .= '
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td><td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                    }

                    $html .= '</tr>';
                }

                $tempParent1 = $row->parent_id;
                $tempParent2 = $row->parentSub->parent_id;
            }
        }elseif($level == '4'){
            $tempParent1 = 0;
            $tempParent2 = 0;
            $tempParent3 = 0;
            foreach($coas as $keymain => $row){
                if($tempParent3 !== $row->parentSub->parentSub->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>'.$row->parentSub->parentSub->parentSub->name.'</b></td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                    foreach($arrMonth as $key => $rowMonth) {
                        $arrMonth[$key]['tempBalanceBefore'] = 0;
                        $arrMonth[$key]['tempDebit'] = 0;
                        $arrMonth[$key]['tempCredit'] = 0;
                        $arrMonth[$key]['tempBalance'] = 0;
                    }
                }
                if($tempParent2 !== $row->parentSub->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;<b>'.$row->parentSub->parentSub->name.'</b></td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                }
                if($tempParent1 !== $row->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row->parentSub->name.'</td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                }
                $html .= '<tr>
                    <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row->name.'</td>';

                foreach($arrMonth as $key => $rowMonth) {
                    $val = $row->getTotalMonthFromParent($rowMonth['raw_month'],$level);
                    $html .= '
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalDebit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalCredit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'] + $val['totalBalance'],2,',','.').'</td>';
                    $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                    $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
                }

                $html .= '</tr>';

                if(isset($coas[$keymain + 1])){
                    if($coas[$keymain + 1]->parentSub->parentSub->parent_id !== $row->parentSub->parentSub->parent_id){
                        $html .= '<tr>
                            <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->parentSub->parentSub->name.'</b></td>';
                        
                        foreach($arrMonth as $key => $rowMonth) {
                            $html .= '<td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                        }
                        
                        $html .= '</tr>';
                    }
                }else{
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->parentSub->parentSub->name.'</b></td>';

                    foreach($arrMonth as $key => $rowMonth) {
                        $html .= '<td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                    }

                    $html .= '</tr>';
                }

                $tempParent1 = $row->parent_id;
                $tempParent2 = $row->parentSub->parent_id;
                $tempParent3 = $row->parentSub->parentSub->parent_id;
            }
        }elseif($level == '5'){
            $tempParent1 = 0;
            $tempParent2 = 0;
            $tempParent3 = 0;
            $tempParent4 = 0;
            foreach($coas as $keymain => $row){
                if($tempParent4 !== $row->parentSub->parentSub->parentSub->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>'.$row->parentSub->parentSub->parentSub->parentSub->name.'</b></td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                    foreach($arrMonth as $key => $rowMonth) {
                        $arrMonth[$key]['tempBalanceBefore'] = 0;
                        $arrMonth[$key]['tempDebit'] = 0;
                        $arrMonth[$key]['tempCredit'] = 0;
                        $arrMonth[$key]['tempBalance'] = 0;
                    }
                }
                if($tempParent3 !== $row->parentSub->parentSub->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;<b>'.$row->parentSub->parentSub->parentSub->name.'</b></td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                }
                if($tempParent2 !== $row->parentSub->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>'.$row->parentSub->parentSub->name.'</b></td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                }
                if($tempParent1 !== $row->parent_id){
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row->parentSub->name.'</td>
                        <td colspan="'.(count($arrMonth) * 4).'"></td>
                    </tr>';
                }
                $html .= '<tr>
                    <td style="left: 0px;position: sticky;background-color:white;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row->name.'</td>';

                foreach($arrMonth as $key => $rowMonth) {
                    $val = $row->getTotalMonthFromParent($rowMonth['raw_month'],$level);
                    $html .= '
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalDebit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalCredit'],2,',','.').'</td>
                        <td style="min-width:150px !important;" class="right-align">'.number_format($val['totalBalanceBefore'] + $val['totalBalance'],2,',','.').'</td>';
                    $arrMonth[$key]['totalDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['totalCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['totalBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempDebit'] += $val['totalDebit'];
                    $arrMonth[$key]['tempCredit'] += $val['totalCredit'];
                    $arrMonth[$key]['tempBalance'] += $val['totalBalanceBefore'] + $val['totalDebit'] - $val['totalCredit'];
                    $arrMonth[$key]['tempBalanceBefore'] += $val['totalBalanceBefore'];
                    $arrMonth[$key]['totalBalanceBefore'] += $val['totalBalanceBefore'];
                }

                $html .= '</tr>';

                if(isset($coas[$keymain + 1])){
                    if($coas[$keymain + 1]->parentSub->parentSub->parentSub->parent_id !== $row->parentSub->parentSub->parentSub->parent_id){
                        $html .= '<tr>
                            <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->parentSub->parentSub->parentSub->name.'</b></td>';
                        
                        foreach($arrMonth as $key => $rowMonth) {
                            $html .= '<td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                            <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                        }
                        
                        $html .= '</tr>';
                    }
                }else{
                    $html .= '<tr>
                        <td style="left: 0px;position: sticky;background-color:white;"><b>TOTAL '.$row->parentSub->parentSub->parentSub->parentSub->name.'</b></td>';

                    foreach($arrMonth as $key => $rowMonth) {
                        $html .= '<td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalanceBefore'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempDebit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempCredit'],2,',','.').'</b></td>
                        <td style="min-width:150px !important;" class="right-align"><b>'.number_format($rowMonth['tempBalance'],2,',','.').'</b></td>';
                    }

                    $html .= '</tr>';
                }

                $tempParent1 = $row->parent_id;
                $tempParent2 = $row->parentSub->parent_id;
                $tempParent3 = $row->parentSub->parentSub->parent_id;
                $tempParent4 = $row->parentSub->parentSub->parentSub->parent_id;
            }
        }

        $html .= '</tbody><tfoot><tr><th class="right-align" style="left: 0px;position: sticky;background-color:white;">TOTAL</th>';

        foreach($arrMonth as $key => $row) {
            $html .= '
                <th style="min-width:150px !important;" class="right-align">'.number_format($row['totalBalanceBefore'],2,',','.').'</th>
                <th style="min-width:150px !important;" class="right-align">'.number_format($row['totalDebit'],2,',','.').'</th>
                <th style="min-width:150px !important;" class="right-align">'.number_format($row['totalCredit'],2,',','.').'</th>
                <th style="min-width:150px !important;" class="right-align">'.number_format($row['totalBalance'],2,',','.').'</th>';
        }

        $html .= '</tr></tfoot></table>
            <script>
                var element = $(".sidebar-sticky"),
                    originalY = element.offset().top;

                var topMargin = 68;

                element.css("position", "relative");
                element.css("border", "1px solid;");
                
                $(window).on("scroll", function(event) {
                    var scrollTop = $(window).scrollTop();
                    var imgtop = scrollTop < originalY ? 0 : scrollTop - originalY + topMargin;
                    element.css("top", imgtop + "px");
                });
            </script>
        ';
        

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
