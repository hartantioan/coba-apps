<?php

namespace App\Exports;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Illuminate\Support\Facades\Log;
use App\Models\Variety;
use App\Models\Type;
use App\Models\Pallet;
use App\Models\Unit;
use App\Models\User;

class ExportTemplateMasterMitraPriceList implements WithEvents{

    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event){
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_mitra_price_list_import.xlsx')),Excel::XLSX);
        
        // Sheet Data Variety #1
        $variety = Variety::where('status','1')->get();
        $startrow = 2;
        foreach($variety as $row){
            $event->getWriter()->getSheetByIndex(1)->setCellValue('A'.$startrow,$row->code);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('B'.$startrow,$row->name);
            $event->getWriter()->getSheetByIndex(1)->setCellValue('C'.$startrow,$row->code."#".$row->name);
            $startrow++;
        }

        // Data Validation Variety di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('B2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Pilih Data Variety');
        $validation->setPrompt('Pilih data yang tersedia pada drop-down list.');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Error');
        $validation->setError('Data tidak ada di list.');
        $validation->setFormula1('\'variety\'!$C$2:$C$'.($startrow>2 ? $startrow-1 : $startrow));
        $validation->setSqref('B2:B4');

        // Sheet Data Type #2
        $type = Type::where('status','1')->get();
        $startrow = 2;
        foreach($type as $row){
            $event->getWriter()->getSheetByIndex(2)->setCellValue('A'.$startrow,$row->code);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('B'.$startrow,$row->name);
            $event->getWriter()->getSheetByIndex(2)->setCellValue('C'.$startrow,$row->code."#".$row->name);
            $startrow++;
        }

        // Data Validation Type di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('C2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Pilih Data Type');
        $validation->setPrompt('Pilih data yang tersedia pada drop-down list.');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Error');
        $validation->setError('Data tidak ada di list.');
        $validation->setFormula1('\'type\'!$C$2:$C$'.($startrow>2 ? $startrow-1 : $startrow));
        $validation->setSqref('C2:C4');
        
        // Sheet Data Package #3
        $package = Pallet::where('status','1')->get();
        $startrow = 2;
        foreach($package as $row){
            $event->getWriter()->getSheetByIndex(3)->setCellValue('A'.$startrow,$row->prefix_code);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('B'.$startrow,$row->name);
            $event->getWriter()->getSheetByIndex(3)->setCellValue('C'.$startrow,$row->prefix_code."#".$row->name);
            $startrow++;
        }

        // Data Validation Package di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('D2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Pilih Data Package');
        $validation->setPrompt('Pilih data yang tersedia pada drop-down list.');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Error');
        $validation->setError('Data tidak ada di list.');
        $validation->setFormula1('\'packaging\'!$C$2:$C$'.($startrow>2 ? $startrow-1 : $startrow));
        $validation->setSqref('D2:D4');
        
        // Sheet Data UOM #4
        $uom = Unit::where('status','1')->get();
        $startrow = 2;
        foreach($uom as $row){
            $event->getWriter()->getSheetByIndex(4)->setCellValue('A'.$startrow,$row->code);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('B'.$startrow,$row->name);
            $event->getWriter()->getSheetByIndex(4)->setCellValue('C'.$startrow,$row->code."#".$row->name);
            $startrow++;
        }

        // Data Validation UOM di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('F2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Pilih Data UOM');
        $validation->setPrompt('Pilih data yang tersedia pada drop-down list.');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Error');
        $validation->setError('Data tidak ada di list.');
        $validation->setFormula1('\'uom\'!$C$2:$C$'.($startrow>2 ? $startrow-1 : $startrow));
        $validation->setSqref('F2:F4');
        
        // Sheet Data Broker #5
        $mitra = User::where('status','1')->where('type','5')->get();
        $startrow = 2;
        foreach($mitra as $row){
            $event->getWriter()->getSheetByIndex(5)->setCellValue('A'.$startrow,$row->employee_no);
            $event->getWriter()->getSheetByIndex(5)->setCellValue('B'.$startrow,$row->name);
            $event->getWriter()->getSheetByIndex(5)->setCellValue('C'.$startrow,$row->employee_no."#".$row->name);
            $startrow++;
        }

        // Data Validation Broker di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('J2')->getDataValidation();
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
        $validation->setFormula1('\'mitra\'!$C$2:$C$'.($startrow>2 ? $startrow-1 : $startrow));
        $validation->setSqref('J2:J4');

        // Data Validation Price Group di main sheet
        $validation = $event->getWriter()->getSheetByIndex(0)->getCell('K2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"RTL"');
        $validation->setSqref('K2:K4');

        return $event->getWriter()->getSheetByIndex(0);
    }
}