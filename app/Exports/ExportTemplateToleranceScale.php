<?php

namespace App\Exports;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportTemplateToleranceScale extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements WithCustomValueBinder, WithEvents
{
    use Exportable, RegistersEventListeners;
    public static function beforeExport(BeforeExport $event)
    {
        $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path('app/public/format_imports/format_tolerance_scale.xlsx')),Excel::XLSX);
        $event->writer->getSheetByIndex(0);
        return $event->getWriter()->getSheetByIndex(0);
    }
}
