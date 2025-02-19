<?php

namespace App\Exports;

use App\Models\AssetGroup;
use App\Models\CostDistribution;
use App\Models\Division;
use App\Models\HardwareItem;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Place;
use App\Models\Project;
use App\Models\Unit;
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
        $event->writer->getSheetByIndex(4);
        $event->writer->getSheetByIndex(5);
        $event->writer->getSheetByIndex(6);
        $event->writer->getSheetByIndex(7);
        $event->writer->getSheetByIndex(8);
        $event->writer->getSheetByIndex(9);
        $event->writer->getSheetByIndex(10);
        $asset_group = AssetGroup::where('status','1')->orderBy('code')->get();
        $place = Place::where('status','1')->get();
        $inventaris = HardwareItem::where('status','1')->get();
        $costdist = CostDistribution::where('status','1')->get();
        $line = Line::where('status','1')->get();
        $machine = Machine::where('status','1')->get();
        $division = Division::where('status','1')->get();
        $project = Project::where('status','1')->get();
        $unit = Unit::where('status','1')->get();
        
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

        $startRow = 2;
        foreach($inventaris as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->item);
            $startRow++;
        }

        $startRow = 2;
        foreach($costdist as $row){
            $event->getWriter()->getSheetByIndex(5)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(5)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($line as $row){
            $event->getWriter()->getSheetByIndex(6)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(6)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($machine as $row){
            $event->getWriter()->getSheetByIndex(7)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(7)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($division as $row){
            $event->getWriter()->getSheetByIndex(8)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(8)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($project as $row){
            $event->getWriter()->getSheetByIndex(9)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(9)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($project as $row){
            $event->getWriter()->getSheetByIndex(10)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(10)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        return $event->getWriter()->getSheetByIndex(0);
    }
}
