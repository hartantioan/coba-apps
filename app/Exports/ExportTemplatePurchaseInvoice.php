<?php
namespace App\Exports;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use App\Models\Coa;
use App\Models\Department;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Place;
use App\Models\Project;
use App\Models\Tax;
use App\Models\Warehouse;

class ExportTemplatePurchaseInvoice implements WithEvents

{
    use Exportable, RegistersEventListeners;

    public static function beforeExport(BeforeExport $event)
    {
        // get your template file
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_copas_ap_invoice_2.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #main
        $event->writer->getSheetByIndex(1); #coa
        $event->writer->getSheetByIndex(2); #ppn
        $event->writer->getSheetByIndex(3); #pph
        $event->writer->getSheetByIndex(4); #plant
        $event->writer->getSheetByIndex(5); #gudang
        $event->writer->getSheetByIndex(6); #departemen
        $event->writer->getSheetByIndex(7); #line
        $event->writer->getSheetByIndex(8); #mesin
        $event->writer->getSheetByIndex(9); #proyek
        
        // fill with information
        $coa = Coa::where('status','1')->whereDoesntHave('childSub')->orderBy('code')->get();
        $tax = Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get();
        $wtax = Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get();
        $plant = Place::where('status','1')->get();
        $warehouse = Warehouse::where('status','1')->get();
        $department = Department::where('status','1')->get();
        $line = Line::where('status','1')->get();
        $machine = Machine::where('status','1')->get();
        $project = Project::where('status','1')->get();
        
        $startRow = 2;
        foreach($coa as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($tax as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($wtax as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('c'.$startRow,$row->percentage);
            $startRow++;
        }

        $startRow = 2;
        foreach($plant as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($warehouse as $row){
            $event->getWriter()->getSheetByIndex(5)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(5)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($department as $row){
            $event->getWriter()->getSheetByIndex(6)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(6)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }
        
        $startRow = 2;
        foreach($line as $row){
            $event->getWriter()->getSheetByIndex(7)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(7)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($machine as $row){
            $event->getWriter()->getSheetByIndex(8)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(8)->setCellValue('B'.$startRow,$row->name);
            $event->getWriter()->getSheetByIndex(8)->setCellValue('C'.$startRow,$row->line->code);
            $startRow++;
        }

        $startRow = 2;
        foreach($project as $row){
            $event->getWriter()->getSheetByIndex(9)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(9)->setCellValue('B'.$startRow,$row->name);
            $event->getWriter()->getSheetByIndex(9)->setCellValue('C'.$startRow,$row->note);
            $startRow++;
        }

        return $event->getWriter()->getSheetByIndex(0);
    }   

}