<?php

namespace App\Exports;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use App\Models\Menu;
use App\Models\Place;
use App\Models\User;
use App\Models\Warehouse;
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

        $menu = Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get();
        $place = Place::where('status','1')->orderBy('code')->get();
        $warehouse = Warehouse::where('status','1')->orderBy('name')->get();

        return view('admin.exports.data_access', [
            'user'      => $user,
            'menu'      => $menu,
            'place'     => $place,
            'warehouse' => $warehouse,
        ]);
    }
}
