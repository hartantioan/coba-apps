<?php

namespace App\Exports;

use App\Models\SampleTestInput;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithDrawings;


class ExportReportTestResult implements FromView, ShouldAutoSize, WithDrawings
{
    protected $start_date, $end_date, $data; // ✅ Store data

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ?: '';
        $this->end_date = $end_date ?: '';

        $this->data = SampleTestInput::where(function ($query) {
            if ($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } elseif ($this->start_date) {
                $query->whereDate('post_date', '>=', $this->start_date);
            } elseif ($this->end_date) {
                $query->whereDate('post_date', '<=', $this->end_date);
            }
        })->get();
    }

    public function view(): View
    {
        activity()
            ->performedOn(new SampleTestInput())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Sample Test.');

        return view('admin.exports.sample_test_input', [
            'data' => $this->data, // ✅ Use stored data
        ]);
    }

    public function drawings()
    {
        $drawings = [];

        foreach ($this->data as $index => $row_data) { // ✅ Use stored data
            if (!empty($row_data->document) && file_exists(storage_path('app/' . $row_data->document))) {
                $drawing = new Drawing();
                $drawing->setName('Document Image');
                $drawing->setDescription('Sample Document');
                $drawing->setPath(storage_path('app/' . $row_data->document));
                $drawing->setHeight(100);
                $drawing->setCoordinates('U' . ($index + 2));
                $drawings[] = $drawing;
            }

            if ($row_data->type == '2' && !empty($row_data->sampleTestResultQc->document) &&
                file_exists(storage_path('app/' . $row_data->sampleTestResultQc->document))) {
                $drawing = new Drawing();
                $drawing->setName('QC Document');
                $drawing->setDescription('QC Test Result');
                $drawing->setPath(storage_path('app/' . $row_data->sampleTestResultQc->document));
                $drawing->setHeight(100);
                $drawing->setCoordinates('AB' . ($index + 2));
                $drawings[] = $drawing;
            }

            if ($row_data->type == '1' && !empty($row_data->sampleTestResultProc->document) &&
                file_exists(storage_path('app/' . $row_data->sampleTestResultProc->document))) {
                $drawing = new Drawing();
                $drawing->setName('Proc Document');
                $drawing->setDescription('Proc Test Result');
                $drawing->setPath(storage_path('app/' . $row_data->sampleTestResultProc->document));
                $drawing->setHeight(100);
                $drawing->setCoordinates('AB' . ($index + 2));
                $drawings[] = $drawing;
            }
        }

        return $drawings;
    }
}

