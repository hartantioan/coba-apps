<?php

namespace App\Exports;

use App\Models\Coa;
use App\Models\Place;
use App\Models\Unit;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplateMasterResource implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_resource_import.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #main
        $event->writer->getSheetByIndex(1); #place
        $event->writer->getSheetByIndex(2); #resource group
        $event->writer->getSheetByIndex(3); #unit
    
        $coa = Coa::where('status', '1')->where('level',5)->oldest('code')->get();
        $place = Place::where('status','1')->get();
        $unit = Unit::where('status','1')->get();
        
        $startRow = 2;
        foreach($place as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $startRow++;
        }

        $startRow = 2;
        foreach($coa as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($unit as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        return $event->getWriter()->getSheetByIndex(0);
    }
}
