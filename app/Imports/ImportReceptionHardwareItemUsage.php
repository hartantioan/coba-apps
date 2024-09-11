<?php

namespace App\Imports;

use App\Models\ReceptionHardwareItemsUsage;
use App\Exceptions\RowImportException;
use App\Models\Division;
use App\Models\HardwareItem;
use App\Models\Item;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Menu;
use App\Models\Place;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportReceptionHardwareItemUsage implements WithMultipleSheets
{
    protected $error = '';
    protected $temp = [];

    public function sheets(): array
    {
        activity()
        ->performedOn(new ReceptionHardwareItemsUsage())
        ->causedBy(session('bo_id'))
        ->withProperties(null)
        ->log('Add / edit from excel reception hardware item data.');

        return [
            0 => new handleReceptionHardwareItem($this->temp),
           
        ];
    }

    
}

class handleReceptionHardwareItem implements OnEachRow, WithHeadingRow
{
    public $error = null;
    protected $temp;

    public function __construct(&$temp)
    {
        $this->temp = &$temp;
    }

    public function onRow(Row $row)
    {
        DB::beginTransaction();
        try {
            if (isset($row['item']) && $row['item']) {
               

                $note = $row['catatan'];$divisi=$row['divisi'];$location=$row['lokasi'];
                $item = HardwareItem::where('code', explode('#', $row['item'])[0])->first();
                $user = User::where('employee_no',explode('#', $row['user'])[0])->first();
                if(!$item && $this->error ==null){
                    $this->error = "Item ada yang salah.";
                }elseif(!$note && $this->error ==null){
                    $this->error = "Belum ada keterangan.";
                }

                $dateTime1 = DateTime::createFromFormat('U', ($row['tanggal'] - 25569) * 86400);
                $dateFormatted1 = $dateTime1->format('Y/m/d');
               
                    $query = ReceptionHardwareItemsUsage::create([
                        'code' => ReceptionHardwareItemsUsage::generateCode(),
                        'user_id'           => session('bo_id'),
                        'account_id'        => $user->id,
                        'hardware_item_id'  => $item->id,
                        'info'              => $note,
                        'date'              => now(),
                        'division'          => $divisi,
                        'reception_date'    => $dateFormatted1,
                        'status'            => 1,
                        'status_item'       => 1,
                        'location'			=> $location,
                    ]);
                    
                   
            }else{
                return null;
            } 
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='Header';
            throw new RowImportException($e->getMessage(), $row->getIndex(),$this->error,$sheet);
        }
    }
    
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
