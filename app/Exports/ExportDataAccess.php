<?php

namespace App\Exports;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use App\Models\Menu;
use App\Models\User;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDataAccess implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $employees;

    public function __construct(string $employees)
    {
        $this->employees = $employees ? $employees : '';
    }

    public function view(): View
    {
        $employees = explode(',',$this->employees);
        
        $user = User::where(function($query)use($employees){
            if($this->employees){
                $query->whereIn('id',$employees);
            }else{
                $query->where('status','1');
            }
        })->where('type','1')->get();

        $data = [];        

        foreach($user as $key => $row){
            $menu_access = [];
            $menus = $row->menuDistinct();
            foreach($menus as $rowidmenu){
                $menu = Menu::find($rowidmenu);
                $menu_name = $menu->name;
                $menu_id = $menu->id;
                $arrAccess = []; 
                foreach($row->menuUser()->where('menu_id',$menu->id)->get() as $rowmenu){
                    $arrAccess[] = $rowmenu->type;
                }
                $menu_access[] = [
                    'id'        => $menu_id,
                    'name'      => $menu_name,
                    'access'    => implode(',',$arrAccess),
                ];
            }
            $row['menu_access'] = $menu_access;
            $data[] = $row;
        }

        return view('admin.exports.data_access', [
            'data' => $data,
        ]);
    }
}
