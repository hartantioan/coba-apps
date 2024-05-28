<?php

namespace App\Exports;

use App\Models\Brand;
use App\Models\Grade;
use App\Models\ItemGroup;
use App\Models\Pallet;
use App\Models\Pattern;
use App\Models\Size;
use App\Models\Type;
use App\Models\Unit;
use App\Models\Variety;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;


class ExportTemplateMasterItem implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_item_import.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #main
        $event->writer->getSheetByIndex(1); #item group
        $event->writer->getSheetByIndex(2); #unit
        $event->writer->getSheetByIndex(3);
        $event->writer->getSheetByIndex(4);
        $event->writer->getSheetByIndex(5);
        $event->writer->getSheetByIndex(6);
        $event->writer->getSheetByIndex(7);
        $event->writer->getSheetByIndex(8);
        $event->writer->getSheetByIndex(9);
    
        $item_group = ItemGroup::where('status','1')->whereDoesntHave('childSub')->orderBy('code')->get();
        $unit = Unit::where('status','1')->get();
        $type = Type::where('status','1')->get();
        $size = Size::where('status','1')->get();
        $variety = Variety::where('status','1')->get();
        $pattern = Pattern::where('status','1')->get();
        $pallet = Pallet::where('status','1')->get();
        $grade = Grade::where('status','1')->get();
        $brand = Brand::where('status','1')->get();
        
        $startRow = 2;
        foreach($item_group as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($unit as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($type as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($size as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($variety as $row){
            $event->getWriter()->getSheetByIndex(5)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(5)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($pattern as $row){
            $event->getWriter()->getSheetByIndex(6)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(6)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($pallet as $row){
            $event->getWriter()->getSheetByIndex(7)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(7)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($grade as $row){
            $event->getWriter()->getSheetByIndex(8)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(8)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($brand as $row){
            $event->getWriter()->getSheetByIndex(9)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(9)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        return $event->getWriter()->getSheetByIndex(0);
    }
}
