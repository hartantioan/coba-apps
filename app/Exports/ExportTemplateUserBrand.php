<?php

namespace App\Exports;

use App\Models\Brand;
use App\Models\User;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplateUserBrand implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_import_user_brand.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #header
        $event->writer->getSheetByIndex(1);

        $Brand = Brand::where('status',1)->get();

        $startRow = 2;
        foreach($Brand as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;




        return $event->getWriter()->getSheetByIndex(0);
    }
}
