<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Currency;
use App\Models\User;
use App\Models\Coa;
use App\Models\Place;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Department;
use App\Models\Division;
use App\Models\Project;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;


class ExportTemplateJournalCopy implements WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_copas_journal_2.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0); #main
        $event->writer->getSheetByIndex(1); #item group
        $event->writer->getSheetByIndex(2); #unit
    
        $company = Company::where('status','1')->get();
        $currency = Currency::where('status','1')->get();
        $user = User::where('status','1')->get();
        $coa = Coa::where('status','1')->where('level','5')->get();
        $place = Place::where('status','1')->get();
        $line = Line::where('status','1')->get();
        $machine = Machine::where('status','1')->get();
        $department = Division::where('status','1')->get();
        $project = Project::where('status','1')->get();
        
        $startRow = 2;
        foreach($company as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($currency as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($user as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startRow,$row->employee_no);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($coa as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startRow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startRow,$row->name);
            $startRow++;
        }

        $startRow = 2;
        foreach($place as $row){
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
        foreach($department as $row){
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

        return $event->getWriter()->getSheetByIndex(0);
    }
}
