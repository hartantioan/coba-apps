<?php

namespace App\Exports;

use App\Models\HardwareItem;
use App\Models\User;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplateReceptionHardwareItemUsage implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_reception_hardware.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #header
        $event->writer->getSheetByIndex(1); #alternative
        $event->writer->getSheetByIndex(2);

        $hardwareItem =  HardwareItem::whereHas('receptionHardwareItemsUsage')
        ->whereHas('receptionHardwareItemsUsage', function ($query) {
            $query->where('status', '1');
        }, '=', 0)
        ->orDoesntHave('receptionHardwareItemsUsage')
        ->where('status', '1')
        ->get();
        $employee = User::where('status','1')
        ->where('type','1')->get();
        
        $startRow = 2;
        foreach($hardwareItem as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startRow,$row->item);
            $startRow++;
        }
        
        $startRow = 2;
        foreach($employee as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->employee_no);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        
        

        return $event->getWriter()->getSheetByIndex(0);
    }
}
