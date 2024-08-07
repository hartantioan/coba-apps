<?php

namespace App\Exports;

use App\Models\GoodIssue;
use App\Models\GoodIssueRequestDetail;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportOutstandingGoodIssueRequest implements FromView,ShouldAutoSize
{
    
    public function view(): View
    {
        $data = GoodIssueRequestDetail::whereHas('goodIssueRequest',function($query){
            $query->whereIn('status',['2']);
        })->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->goodIssueRequest->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->goodIssueRequest->post_date));
            $entry["note"] = $row->goodIssueRequest->note;
            $entry["status"] = $row->goodIssueRequest->statusRaw();
            $entry["voider"] = $row->goodIssueRequest->voidUser->name ?? '';
            $entry["void_date"] = $row->goodIssueRequest->void_date ?? '';
            $entry["void_note"] = $row->goodIssueRequest->void_note ?? '';
            $entry["deleter"] = $row->goodIssueRequest->deleteUser->name ??  ' ';
            $entry["delete_date"] = $row->goodIssueRequest->deleted_at ?? '';
            $entry["delete_note"] = $row->goodIssueRequest->delete_note;
            $entry["user"] = $row->goodIssueRequest->user->name;
            $entry["item_code"] = $row->item->code;
            $entry["item_name"] = $row->item->name;
            $entry["plant"] = $row->place->code;
            $entry["ket1"] = $row->note;
            $entry["ket2"] = $row->note2;
            $entry["satuan"] =$row->item->uomUnit->code;
            $entry["qty"] = $row->qty;
            $entry["qty_gi"] = $row->totalGi();
            $entry["qty_balance"] = $row->balanceGi();
            $entry["required_date"] =$row->required_date;
            $entry["warehouse"] =$row->warehouse->name;
            $entry["line"] =$row->line->name ?? '-';
            $entry["machine"] =$row->machine->name ?? '-';
            $entry["divisi"] =$row->department->name ?? '-';
            $entry["project"] =$row->project->name ?? '-';
            $entry["requester"] =$row->requester ?? '-';
            $entry["status_item"] =$row->statusConvert();
            if($row->balanceGi() > 0){
                $array[] = $entry;
            }
        }
        activity()
            ->performedOn(new GoodIssueRequestDetail())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export outstanding good issue request.');
        
        return view('admin.exports.outstanding_good_issue_request', [
            'data' => $array,
            
        ]);
    }
}
