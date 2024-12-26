<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ExportCsvFromFile implements FromCollection, WithCustomCsvSettings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data); // Return the imported data
    }

    /**
     * Define the headings for the CSV file (optional)
     */
    
     public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',  // You can set a different delimiter here if needed
            'enclosure' => '"',  // Enclosure for text values
            'escape' => '\\',    // Escape character for CSV
        ];
    }
}