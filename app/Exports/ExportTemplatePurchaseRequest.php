<?php

namespace App\Exports;

use App\Models\Division;
use App\Models\Item;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Project;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplatePurchaseRequest implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_purchase_request.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #header
        $event->writer->getSheetByIndex(1); #alternative
        $event->writer->getSheetByIndex(2); #detail
        $event->writer->getSheetByIndex(3);
        $event->writer->getSheetByIndex(4);
        $event->writer->getSheetByIndex(5);
        $event->writer->getSheetByIndex(6);

        $Line =  Line::where('status',1)->get();
        $Machine = Machine::where('status',1)->get();
        $Division =Division::where('status',1)->get();
        $Project = Project::where('status',1)->get();
        $Item = Item::where('status',1)->get();
        $startRow = 2;
        foreach($Line as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        
        $startRow = 2;
        foreach($Machine as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        $startRow = 2;
        foreach($Division as $row){
            $event->getWriter()->getSheetByIndex(5)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(5)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
       

        $startRow = 2;
        foreach($Project as $row){
            $event->getWriter()->getSheetByIndex(6)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(6)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($Item as $row){
            $event->getWriter()->getSheetByIndex(7)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(7)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        

        return $event->getWriter()->getSheetByIndex(0);
    }
}
