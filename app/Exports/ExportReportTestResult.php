<?php

namespace App\Exports;

use App\Models\SampleTestInput;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithDrawings;


class ExportReportTestResult implements FromView, ShouldAutoSize
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

    // public function drawings()
    // {
    //     $drawings = [];

    //     foreach ($this->data as $index => $row_data) {
    //         // General document(s)
    //         $this->addDrawingsFromString($drawings, $row_data->document, 'Document Image', 'Sample Document', 'U' . ($index + 2));

    //         // Conditional QC and Proc documents
    //         if ($row_data->type == '2') {
    //             $this->addDrawingsFromString($drawings, optional($row_data->sampleTestResultQc)->document, 'QC Document', 'QC Test Result', 'AB' . ($index + 2));
    //         } elseif ($row_data->type == '3') {
    //             $this->addDrawingsFromString($drawings, optional($row_data->sampleTestResultQcPacking)->document, 'QC Document', 'QC Test Result', 'AB' . ($index + 2));
    //         } elseif ($row_data->type == '1') {
    //             $this->addDrawingsFromString($drawings, optional($row_data->sampleTestResultProc)->document, 'Proc Document', 'Proc Test Result', 'AB' . ($index + 2));
    //         }
    //     }

    //     return $drawings;
    // }

    // // // Helper function to process multiple documents
    // // private function addDrawingsFromString(&$drawings, $documents, $name, $description, $coordinates)
    // // {
    // //     if (empty($documents)) return;

    // //     $files = explode(',', $documents);

    // //     foreach ($files as $file) {
    // //         $file = trim($file); // Remove spaces

    // //         if (Storage::exists($file) && $this->isImage($file)) {
    // //             $drawing = new Drawing();
    // //             $drawing->setName($name);
    // //             $drawing->setDescription($description);
    // //             $drawing->setPath(storage_path('app/' . $file));
    // //             $drawing->setHeight(100);
    // //             $drawing->setCoordinates($coordinates);
    // //             $drawings[] = $drawing;
    // //         }
    // //     }
    // // }

    // // // Function to check if a file is an image
    // // private function isImage($file)
    // // {
    // //     $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    // //     $extension = pathinfo($file, PATHINFO_EXTENSION);

    // //     return in_array(strtolower($extension), $allowedExtensions);
    // // }


}

