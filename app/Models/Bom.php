<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

class Bom extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'boms';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'user_id',
        'item_id',
        'place_id',
        'warehouse_id',
        'qty_output',
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine(){
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function bomDetail(){
        return $this->hasMany('App\Models\BomDetail');
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public function type(){
        switch($this->type) {
            case '1':
                $status = 'Perakitan';
                break;
            case '2':
                $status = 'Penjualan';
                break;
            case '3':
                $status = 'Produksi';
                break;
            case '4':
                $status = 'Template';
                break;
            default:
                $status = 'Invalid';
                break;
        }

        return $status;
    }

    public function arrComposition(){
        $arr = [];

        foreach($this->bomDetail as $row){
            if($row->item()->exists()){
                $item1 = $row->item;
                $bom1 = $item1->bom()->orderByDesc('id')->first();
                if($bom1){
                    $arrMain = [];
                    foreach($bom1->bomDetail as $row1){              
                        if($row1->item()->exists()){
                            $item2 = $row1->item;
                            $bom2 = $item2->bom()->orderByDesc('id')->first();
                            if($bom2){
                                $arr1 = [];
                                foreach($bom2->bomDetail as $row2){
                                    if($row2->item()->exists()){
                                        $item3 = $row2->item;
                                        $bom3 = $item3->bom()->orderByDesc('id')->first();
                                        if($bom3){
                                            $arr2 = [];
                                            foreach($bom3->bomDetail as $row3){
                                                if($row3->item()->exists()){
                                                    $item4 = $row3->item;
                                                    $bom4 = $item4->bom()->orderByDesc('id')->first();
                                                    if($bom4){
                                                        $arr3 = [];
                                                        foreach($bom4->bomDetail as $row4){
                                                            if($row4->item()->exists()){
                                                                $arr3[] = [
                                                                    'item_id'   => $row4->item->id,
                                                                    'item_code' => $row4->item->code,
                                                                    'item_name' => $row4->item->name,
                                                                    'materials' => []
                                                                ];
                                                            }
                                                            if($row4->coa()->exists()){
                                                                $arr3[] = [
                                                                    'coa_id'    => $row4->coa->id,
                                                                    'coa_code'  => $row4->coa->code,
                                                                    'coa_name'  => $row4->coa->name,
                                                                    'materials' => []
                                                                ];
                                                            }
                                                        }
                                                        $row3->materials = $arr3;
                                                    }
                                                    $arr2[] = [
                                                        'item_id'   => $row3->item->id,
                                                        'item_code' => $row3->item->code,
                                                        'item_name' => $row3->item->name,
                                                        'materials' => $row3->materials,
                                                    ];
                                                }
                                                if($row3->coa()->exists()){
                                                    $arr2[] = [
                                                        'coa_id'    => $row3->coa->id,
                                                        'coa_code'  => $row3->coa->code,
                                                        'coa_name'  => $row3->coa->name,
                                                        'materials' => []
                                                    ];
                                                }
                                            }
                                            $row2->materials = $arr2;
                                        }
                                        $arr1[] = [
                                            'item_id'   => $row2->item->id,
                                            'item_code' => $row2->item->code,
                                            'item_name' => $row2->item->name,
                                            'materials' => $row2->materials,
                                        ];
                                    }

                                    if($row2->coa()->exists()){
                                        $arr1[] = [
                                            'coa_id'    => $row2->coa->id,
                                            'coa_code'  => $row2->coa->code,
                                            'coa_name'  => $row2->coa->name,
                                            'materials' => []
                                        ];
                                    }
                                }
                                $row1->materials = $arr1;
                            }
                            $arrMain[] = [
                                'item_id'   => $row1->item->id,
                                'item_code' => $row1->item->code,
                                'item_name' => $row1->item->name,
                                'materials' => $row1->materials,
                            ];
                        }
                        
                        if($row1->coa()->exists()){
                            $arrMain[] = [
                                'coa_id'    => $row1->coa->id,
                                'coa_code'  => $row1->coa->code,
                                'coa_name'  => $row1->coa->name,
                                'materials' => []
                            ];
                        }
                    }
                    $row->materials = $arrMain;
                }
                $arr[] = [
                    'item_id'   => $row->item->id,
                    'item_code' => $row->item->code,
                    'item_name' => $row->item->name,
                    'materials' => $row->materials,
                ];
            }

            if($row->coa()->exists()){
                $arr[] = [
                    'coa_id'    => $row->coa->id,
                    'coa_code'  => $row->coa->code,
                    'coa_name'  => $row->coa->name,
                    'materials' => []
                ];
            }
        }

        return $arr;
    }

    public function arrAvailableComposition(){
        $arr = [];

        foreach($this->bomDetail as $row){
            if($row->item()->exists()){
                $item1 = $row->item;
                $bom1 = $item1->bom()->orderByDesc('id')->first();
                if($bom1){
                    $arrMain = [];
                    foreach($bom1->bomDetail as $row1){
                        if($row1->item()->exists()){
                            $item2 = $row1->item;
                            $bom2 = $item2->bom()->orderByDesc('id')->first();
                            if($bom2){
                                $arr1 = [];
                                foreach($bom2->bomDetail as $row2){
                                    if($row2->item()->exists()){
                                        $item3 = $row2->item;
                                        $bom3 = $item3->bom()->orderByDesc('id')->first();
                                        if($bom3){
                                            $arr2 = [];
                                            foreach($bom3->bomDetail as $row3){
                                                if($row3->item()->exists()){
                                                    $item4 = $row3->item;
                                                    $bom4 = $item4->bom()->orderByDesc('id')->first();
                                                    if($bom4){
                                                        $arr3 = [];
                                                        foreach($bom4->bomDetail as $row4){
                                                            if($row4->item()->exists()){
                                                                $arr3[] = [
                                                                    'item_id'   => $row4->item->id,
                                                                    'item_code' => $row4->item->code,
                                                                    'item_name' => $row4->item->name,
                                                                    'qty'       => $row4->qty,
                                                                    'nominal'   => $row4->nominal
                                                                ];
                                                            }
                                                            if($row4->coa()->exists()){
                                                                $arr3[] = [
                                                                    'coa_id'    => $row4->coa->id,
                                                                    'coa_code'  => $row4->coa->code,
                                                                    'coa_name'  => $row4->coa->name,
                                                                    'qty'       => $row4->qty,
                                                                    'nominal'   => $row4->nominal
                                                                ];
                                                            }
                                                        }
                                                        $arr[] = [
                                                            'item_id'           => $row3->item->id,
                                                            'item_code'         => $row3->item->code,
                                                            'item_name'         => $row3->item->name,
                                                            'qty_in_production' => CustomHelper::formatConditionalQty($row3->qty),
                                                            'unit_production'   => $row3->item->productionUnit->code,
                                                            'qty_output'        => CustomHelper::formatConditionalQty($bom3->qty_output),
                                                            'bom_id'            => $bom4->id,
                                                            'materials'         => $arr3,
                                                            'group'             => $row3->item->itemGroup->production_type,
                                                            'warehouses'        => $row3->item->warehouseList(),
                                                            'item_goal'         => $bom3->item_id,
                                                            'qty_proporsional'  => CustomHelper::formatConditionalQty($row3->qty * $bom3->qty_output),
                                                        ];
                                                    }
                                                    $arr2[] = [
                                                        'item_id'   => $row3->item->id,
                                                        'item_code' => $row3->item->code,
                                                        'item_name' => $row3->item->name,
                                                        'qty'       => $row3->qty,
                                                        'nominal'   => $row3->nominal
                                                    ];
                                                }
                                                if($row3->coa()->exists()){
                                                    $arr2[] = [
                                                        'coa_id'    => $row3->coa->id,
                                                        'coa_code'  => $row3->coa->code,
                                                        'coa_name'  => $row3->coa->name,
                                                        'qty'       => $row3->qty,
                                                        'nominal'   => $row3->nominal
                                                    ];
                                                }
                                            }
                                            $arr[] = [
                                                'item_id'           => $row2->item->id,
                                                'item_code'         => $row2->item->code,
                                                'item_name'         => $row2->item->name,
                                                'qty_in_production' => CustomHelper::formatConditionalQty($row2->qty),
                                                'unit_production'   => $row2->item->productionUnit->code,
                                                'qty_output'        => CustomHelper::formatConditionalQty($bom2->qty_output),
                                                'bom_id'            => $bom3->id,
                                                'materials'         => $arr2,
                                                'group'             => $row2->item->itemGroup->production_type,
                                                'warehouses'        => $row2->item->warehouseList(),
                                                'item_goal'         => $bom2->item_id,
                                                'qty_proporsional'  => CustomHelper::formatConditionalQty($row2->qty * $bom2->qty_output),
                                            ];
                                        }
                                        $arr1[] = [
                                            'item_id'   => $row2->item->id,
                                            'item_code' => $row2->item->code,
                                            'item_name' => $row2->item->name,
                                            'qty'       => $row2->qty,
                                            'nominal'   => $row2->nominal
                                        ];
                                    }
                                    if($row2->coa()->exists()){
                                        $arr1[] = [
                                            'coa_id'    => $row2->coa->id,
                                            'coa_code'  => $row2->coa->code,
                                            'coa_name'  => $row2->coa->name,
                                            'qty'       => $row2->qty,
                                            'nominal'   => $row2->nominal
                                        ];
                                    }
                                }
                                $arr[] = [
                                    'item_id'           => $row1->item->id,
                                    'item_code'         => $row1->item->code,
                                    'item_name'         => $row1->item->name,
                                    'qty_in_production' => CustomHelper::formatConditionalQty($row1->qty),
                                    'unit_production'   => $row1->item->productionUnit->code,
                                    'qty_output'        => CustomHelper::formatConditionalQty($bom1->qty_output),
                                    'bom_id'            => $bom2->id,
                                    'materials'         => $arr1,
                                    'group'             => $row1->item->itemGroup->production_type,
                                    'warehouses'        => $row1->item->warehouseList(),
                                    'item_goal'         => $bom1->item_id,
                                    'qty_proporsional'  => CustomHelper::formatConditionalQty($row1->qty * $bom1->qty_output),
                                ];
                            }
                            $arrMain[] = [
                                'item_id'   => $row1->item->id,
                                'item_code' => $row1->item->code,
                                'item_name' => $row1->item->name,
                                'qty'       => $row1->qty,
                                'nominal'   => $row1->nominal
                            ];
                        }
                        if($row1->coa()->exists()){
                            $arrMain[] = [
                                'coa_id'    => $row1->coa->id,
                                'coa_code'  => $row1->coa->code,
                                'coa_name'  => $row1->coa->name,
                                'qty'       => $row1->qty,
                                'nominal'   => $row1->nominal
                            ];
                        }
                    }
                    $arr[] = [
                        'item_id'           => $row->item->id,
                        'item_code'         => $row->item->code,
                        'item_name'         => $row->item->name,
                        'qty_in_production' => CustomHelper::formatConditionalQty($row->qty),
                        'unit_production'   => $row->item->productionUnit->code,
                        'qty_output'        => CustomHelper::formatConditionalQty($this->qty_output),
                        'bom_id'            => $bom1->id,
                        'materials'         => $arrMain,
                        'group'             => $row->item->itemGroup->production_type,
                        'warehouses'        => $row->item->warehouseList(),
                        'item_goal'         => $this->item_id,
                        'qty_proporsional'  => CustomHelper::formatConditionalQty($row->qty * $this->qty_output),
                    ];
                }
            }
        }

        return $arr;
    }
}
