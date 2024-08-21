<?php

namespace App\Exports;

use App\Models\Region;
use App\Models\Transportation;
use App\Models\User;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplateDeliveryCost implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_import_delivery_cost.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #header
        $event->writer->getSheetByIndex(1); 
        $event->writer->getSheetByIndex(2); 
        $event->writer->getSheetByIndex(3);
        $event->writer->getSheetByIndex(4);
        $user =  User::whereIn('type',['3','4'])->get();
        $city =  Region::whereRaw('LENGTH(code) = 5')->get();
        $subdistrict = Region::whereRaw('LENGTH(code) = 8')->get();
        $transport = Transportation::where('status','1')->get();
        $startRow = 2;
        foreach($user as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->employee_no);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        
        $startRow = 2;
        foreach($city as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        $startRow = 2;
        foreach($subdistrict as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($transport as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        

        return $event->getWriter()->getSheetByIndex(0);
    }
}
