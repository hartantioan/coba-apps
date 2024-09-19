<?php
namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ExportItemFGPicture implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, WithEvents,ShouldAutoSize
{
    protected $search, $status;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ?: '';
        $this->status = $status ?: '';
    }

    private $headings = [
        'NO',
        'CODE',
        'NAME',
        'GAMBAR',
        'USER',
    ];

    public function collection()
    {
        return Item::with(['itemFgpicture' => function ($query) {
                $query->select('id', 'image', 'user_id', 'item_id'); 
            }, 'itemFgpicture.user' => function ($query) {
                $query->select('id', 'name'); 
            }])
            ->select('id', 'code', 'name') 
            ->where(function ($query) {
                if ($this->search) {
                    $query->where('code', 'like', "%$this->search%")
                          ->orWhere('name', 'like', "%$this->search%");
                }
                if ($this->status) {
                    $query->where('status', $this->status);
                }
            })
            ->whereNotNull('is_sales_item')
            ->whereHas('parentFg')
            ->get();
    }

    public function title(): string
    {
        return 'Laporan Gambar Item FG';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $row = 2; 

                
                $items = $this->collection();

                foreach ($items as $index => $item) {
                    
                    $event->sheet->setCellValue('A' . $row, $index + 1); 
                    $event->sheet->setCellValue('B' . $row, $item->code); 
                    $event->sheet->setCellValue('C' . $row, $item->name); 

                   
                    if ($item->itemFgpicture) {
                     
                        $event->sheet->setCellValue('E' . $row, $item->itemFgpicture->user->name ?? '-');

                       
                        $imagePath = storage_path('app/' . $item->itemFgpicture->image);
                        if (file_exists($imagePath)) {
                            $drawing = new Drawing();
                            $drawing->setName('Image');
                            $drawing->setDescription('Image');
                            $drawing->setPath($imagePath);
                            $drawing->setHeight(50); 
                            $drawing->setCoordinates('D' . $row); 
                            $drawing->setWorksheet($event->sheet->getDelegate());
                        } else {
                            info('File does not exist: ' . $imagePath);
                        }
                    } else {
                        
                        $event->sheet->setCellValue('E' . $row, '-'); 
                    }

                    $row++;
                }
            },
        ];
    }
}
