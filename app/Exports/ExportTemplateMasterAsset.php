<?php

namespace App\Exports;

use App\Models\AssetGroup;
use App\Models\Place;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplateMasterAsset implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_asset_import.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #main
        $event->writer->getSheetByIndex(1); #place
        $event->writer->getSheetByIndex(2); #asset group
    
        $asset_group = AssetGroup::where('status','1')->orderBy('code')->get();
        $place = Place::where('status','1')->get();
        
        $startRow = 2;
        foreach($place as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $startRow++;
        }

        $startRow = 2;
        foreach($asset_group as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        return $event->getWriter()->getSheetByIndex(0);
    }
}
