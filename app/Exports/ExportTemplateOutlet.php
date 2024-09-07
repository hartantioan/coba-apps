<?php

namespace App\Exports;

use App\Models\Country;
use App\Models\Group;
use App\Models\GroupOutlet;
use App\Models\Region;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
class ExportTemplateOutlet implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_outlet_import.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #header
        $event->writer->getSheetByIndex(1); #alternative
        $event->writer->getSheetByIndex(2); #detail
        $event->writer->getSheetByIndex(3);
        $event->writer->getSheetByIndex(4);
        $event->writer->getSheetByIndex(5);


        $city =  Region::whereRaw('LENGTH(code) = 5')->get();
        $province = Region::whereRaw('LENGTH(code) = 2')->get();
        $district =Region::whereRaw('LENGTH(code) = 8')->get();
        $country = Country::where('status',1)->get();
        $group = Group::where('status','1')->get();
        $group_outlet = GroupOutlet::where('status','1')->get();
        
        $startRow = 2;
        foreach($city as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        
        $startRow = 2;
        foreach($province as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        $startRow = 2;
        foreach($district as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($group as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        $startRow = 2;
        foreach($group_outlet as $row){
            $event->getWriter()->getSheetByIndex(5)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(5)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
       
        

        return $event->getWriter()->getSheetByIndex(0);
    }
}
