<?php

namespace App\Exports;

use App\Models\GoodScale;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderDeliveryProcess;

class ExportMarketingGoodScale implements FromView, WithEvents
{

    protected $start_date, $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
    }
    public function view(): View
    {

      
        $data = GoodScale::where(function($query) {
            $query->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->end_date)->where('type','2');
        }) ->get();


      

        activity()
            ->performedOn(new GoodScale())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Good Scale Sales.');

        return view('admin.exports.marketing_good_scale', [
            'data'      => $data,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Auto-fit columns A to Z
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->autoSize();
                $event->sheet->freezePane("A1");
            }
        ];
    }
}
