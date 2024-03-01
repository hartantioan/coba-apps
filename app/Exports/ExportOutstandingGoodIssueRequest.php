<?php

namespace App\Exports;

use App\Models\GoodIssue;
use App\Models\goodIssueRequestDetail;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportOutstandingGoodIssueRequest implements FromView,ShouldAutoSize
{
    
    public function view(): View
    {
        $data = GoodIssueRequestDetail::whereHas('goodIssueRequest',function($query){
            $query->whereIn('status',['2','3']);
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
            $entry["satuan"] =$row->itemUnit->unit->code;
            $entry["qty"] = number_format($row->qty,3,',','.');
            $entry["qty_gi"] = number_format($row->totalGi(),3,',','.');
            $entry["qty_balance"] = number_format($row->balanceGi(),3,',','.');
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
        
        
        return view('admin.exports.outstanding_good_issue_request', [
            'data' => $array,
            
        ]);
    }
}
