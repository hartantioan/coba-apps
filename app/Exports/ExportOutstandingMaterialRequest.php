<?php

namespace App\Exports;

use App\Models\MaterialRequestDetail;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportOutstandingMaterialRequest implements FromView,ShouldAutoSize
{
    
    public function view(): View
    {
        $data = MaterialRequestDetail::whereHas('materialRequest',function($query){
            $query->whereIn('status',['2']);
        })->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->materialRequest->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->materialRequest->post_date));
            $entry["note"] = $row->materialRequest->note;
            $entry["status"] = $row->materialRequest->statusRaw();
            $entry["voider"] = $row->materialRequest->voidUser->name ?? '';
            $entry["void_date"] = $row->materialRequest->void_date ?? '';
            $entry["void_note"] = $row->materialRequest->void_note ?? '';
            $entry["deleter"] = $row->materialRequest->deleteUser->name ??  ' ';
            $entry["delete_date"] = $row->materialRequest->deleted_at ?? '';
            $entry["delete_note"] = $row->materialRequest->delete_note;
            $entry["user"] = $row->materialRequest->user->name;
            $entry["item_code"] = $row->item->code;
            $entry["item_name"] = $row->item->name;
            $entry["plant"] = $row->place->code;
            $entry["ket1"] = $row->note;
            $entry["ket2"] = $row->note2;
            $entry["satuan"] =$row->itemUnit->unit->code;
            $entry["qty"] = $row->qty;
            $entry["qty_pr"] = $row->totalPr();
            $entry["qty_gi"] = $row->totalGi();
            $entry["qty_balance"] = $row->balancePrGi();
            $entry["required_date"] =$row->required_date;
            $entry["warehouse"] =$row->warehouse->name;
            $entry["line"] =$row->line->name ?? '-';
            $entry["machine"] =$row->machine->name ?? '-';
            $entry["divisi"] =$row->department->name ?? '-';
            $entry["project"] =$row->project->name ?? '-';
            $entry["requester"] =$row->requester ?? '-';
            $entry["status_item"] =$row->statusConvert();
            if($row->balancePrGi() > 0){
                $array[] = $entry;
            }
            
            
        }
        
        
        return view('admin.exports.outstanding_material_request', [
            'data' => $array,
            
        ]);
    }
}
