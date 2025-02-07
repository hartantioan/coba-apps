<?php

namespace App\Exports;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ExportTemplateMasterMitraSalesArea implements WithEvents{

    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event){
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_mitra_sales_area_import.xlsx')),Excel::XLSX);
        
        // Sheet Data Broker
        $mitra = User::where('status','1')->where('type','5')->get();
        $arrMitra = array();
        $startrow = 2;
        foreach($mitra as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startrow,$row->employee_no);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startrow,$row->name);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('C'.$startrow,$row->employee_no."#".$row->name);
            $arrMitra[] = $row->employee_no."#".$row->name;
            $startrow++;
        }

        // Data Validation Broker di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('D2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Pilih Data Mitra');
        $validation->setPrompt('Pilih data yang tersedia pada drop-down list.');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Error');
        $validation->setError('Data tidak ada di list.');
        $validation->setFormula1('"'.implode(',',$arrMitra).'"');
        $validation->setSqref('D2:D4');

        // Data Validation Type di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('C2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"Kota/Kabupaten"');
        $validation->setSqref('C2:C4');

        return $event->getWriter()->getSheetByIndex(0);
    }
}