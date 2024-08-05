<?php

namespace App\Exports;

use App\Models\Region;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExportRegion extends DefaultValueBinder implements WithCustomValueBinder, FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $search,$level;

    public function __construct(string $search,string $level)
    {
        $this->search = $search ? $search : '';
        $this->level = $level ? $level : '';
    }

    private $headings = [
        'NO',
        'PROVINSI',
        'KABUPATEN',
        'KODE KECAMATAN', 
        'NAMA KECAMATAN',
    ];

    public function collection()
    {
        $data = Region::where(function ($query) {
            $query->whereRaw("CHAR_LENGTH(code) = 8");
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                    ->orWhere('name', 'like', "%$this->search%");
                });
            }

            if($this->level){
                $query->whereRaw("CHAR_LENGTH(code) = ".intval($this->level));
            }
        })->get();
        $array = [];

        foreach($data as $index=>$row){
            $array[] = [
                'NO' => $index,
                'PROVINSI'=>$row->getProvince(),
                'KABUPATEN'=>$row->city(),
                'KODE KECAMATAN'=>$row->code, 
                'NAMA KECAMATAN'=>$row->name,
            ];

        }
        activity()
            ->performedOn(new Region())
            ->causedBy(session('bo_id'))
            ->withProperties($data)
            ->log('Export region data.');
            
        return collect($array);
    }

    public function title(): string
    {
        return 'Laporan Daerah';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() == 'B') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }
}
