<?php

namespace App\Imports;
use App\Helpers\CustomHelper;
use App\Exceptions\RowImportException;
use App\Models\Division;
use App\Models\Item;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Menu;
use App\Models\Place;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use Carbon\Carbon;
use DateTime;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportPurchaseRequest implements WithMultipleSheets
{
    protected $error = '';
    protected $temp = [];

    public function sheets(): array
    {
        return [
            0 => new handlePR($this->temp),
            1 => new handlePRdetail($this->temp),
        ];
    }
}
class handlePR implements OnEachRow, WithHeadingRow
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
            if (isset($row['plant']) && $row['plant']) {
               

                $note = $row['note'];
                $place = Place::where('id', explode('#', $row['plant'])[0])->first();
                if(!$place && $this->error ==null){
                    $this->error = "Plant.";
                }elseif(!$note && $this->error ==null){
                    $this->error = "Belum ada keterangan.";
                }

                $dateTime1 = DateTime::createFromFormat('U', ($row['tgl_post'] - 25569) * 86400);
                $dateFormatted1 = $dateTime1->format('Y/m/d');
                $dateTime2 = DateTime::createFromFormat('U', ($row['tgl_jatuh_tempo'] - 25569) * 86400);
                $dateFormatted2 = $dateTime2->format('Y/m/d');
                $menu = Menu::where('url', 'purchase_request')->first();
                $newcode = PurchaseRequest::generateCode($menu->document_code.date('y',strtotime(Carbon::now())).$place->code);
               
                    $query = PurchaseRequest::create([
                        'code' => $newcode,
                        'company_id'=>1,
                        'status'=>'1',
                        'user_id' => session('bo_id'),
                        'post_date' => $dateFormatted1,
                        'due_date' => $dateFormatted2,
                        'place_id' => $place->id,
                        'note'     => $row['note'],
                    ]);
                    
      
                    $this->temp[]=[
                        'id' => $query->id,
                        'no' => $row['no']
                    ];

                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Purchase Request No. '.$query->code,$query->note,session('bo_id'));
    
              
                    activity()
                        ->performedOn(new PurchaseRequest())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit from excel pr data.');
               
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

class handlePRdetail implements OnEachRow, WithHeadingRow
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
            if ($row['no_header']) {
         
                foreach($this->temp as $row1){
                  
                    if ($row1['no'] == $row['no_header']) {
            
                        $item = str_replace(',', '.', explode('#', $row['kode_barang'])[0]);
                        $itemq = Item::where('code',$item)->first() ?? null;
                   
                        if($itemq){
                            $warehouse = $itemq->warehouse();
                            $defaultbuyunit = $itemq->defaultBuyUnit();
                            $item_unit_id = $defaultbuyunit['item_unit_id'];
                            $conversion = $defaultbuyunit['conversion'];
                        }else{
                            $sheet='Header';
                            throw new RowImportException("item tidak ditemukan", $row->getIndex(),$this->error,$sheet);
                        }
                     
                        
                        $plant = str_replace(',', '.', explode('#', $row['plant'])[0]);
                        $plant_id = Place::where('id',$plant)->first()->id ?? null; 
                 
                        $line = str_replace(',', '.', explode('#', $row['line'])[0]);
                        $line_id = Line::where('code',$line)->first()->id ?? null;
                
                        $machine =  explode('#', $row['mesin'])[0];
                        $machine_id= Machine::where('code',$machine)->first()->id ?? null;
                
                        $divisi = str_replace(',', '.', explode('#', $row['divisi'])[0]);
                        $divisi_id = Division::where('code',$divisi)->first()->id ?? null;
                     
                        $project =  explode('#', $row['proyek'])[0];
                        $project_id= Project::where('code',$project)->first()->id ?? null;
                    
                        $jumlah = $row['jumlah'];$ket1 = $row['ket_1']; $ket2 = $row['ket_2'];
                        $tgl_pakai = $row['tgl_pakai']; $requester= $row['requester'];

         
                        if(!$line_id && $this->error ==null){
                            $this->error = "Line.";
                        }elseif(!$jumlah && $this->error ==null){
                            $this->error = "Jumlah.";
                        }elseif(!$plant_id && $this->error ==null){
                            $this->error = "plant.";
                        }elseif(!$divisi_id && $this->error ==null){
                            $this->error = "Divisi.";
                        }elseif(!$tgl_pakai && $this->error ==null){
                            $this->error = "Tanggal Pakai.";
                        }
                    
                     
                        if(!$this->error){
                            $dateTime1 = DateTime::createFromFormat('U', ($row['tgl_pakai'] - 25569) * 86400);
                            $dateFormatted_duedate = $dateTime1->format('Y/m/d');
                            $query = PurchaseRequestDetail::create([
                                'purchase_request_id' => $row1['id'],
                                'item_id'             => $itemq->id,
                                'place_id'            => $plant_id,
                                'line_id'             => $line_id,
                                'machine_id'          => $machine_id,
                                'qty'              => $jumlah,
                                'note'                => $ket1,
                                'note2'               => $ket2,
                                'required_date'       => $dateFormatted_duedate,
                                'requester'           => $requester,
                                'warehouse_id'        => $warehouse,
                                'item_unit_id'        => $item_unit_id,
                                'qty_conversion'      => $conversion,
                                'department_id'       => $divisi_id,
                                'status'              => '1',
                                'project_id'          => $project_id,
                            ]);
                        }else{
                         
                            $sheet='Header';
                            throw new RowImportException("data kurang lengkap", $row->getIndex(),$this->error,$sheet);
                        }
                        
                    }
                }
                
                
            }else{
                return null;
            } 
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $sheet='Detail';
            throw new RowImportException($e->getMessage(), $row->getIndex(),$this->error,$sheet);
        }
    }
   
    public function startRow(): int
    {
        return 2; // If you want to skip the first row (heading row)
    }
}
