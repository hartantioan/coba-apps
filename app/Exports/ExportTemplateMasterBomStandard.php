<?php

namespace App\Exports;

use App\Models\CostDistribution;
use App\Models\Item;
use App\Models\Place;
use App\Models\Resource;
use App\Models\Warehouse;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplateMasterBomStandard implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_import_bom_standard.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #header
        $event->writer->getSheetByIndex(1); #detail
        $event->writer->getSheetByIndex(2); #items
        $event->writer->getSheetByIndex(3); #resources
        $event->writer->getSheetByIndex(4); #dist biaya
    
        $items = Item::where('status','1')->orderBy('code')->get();
        $resources = Resource::where('status','1')->orderBy('code')->get();
        $costdist = CostDistribution::where('status','1')->orderBy('code')->get();
        $startRow = 2;
        foreach($items as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        $startRow = 2;
        foreach($resources as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        $startRow = 2;
        foreach($costdist as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        

        return $event->getWriter()->getSheetByIndex(0);
    }
}
