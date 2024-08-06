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
                $query->whereIn('id',$employees)->where('status','1');
            }else{
                $query->where('status','1');
            }
        })->where('type','1')->get();

        $menu = Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get();
        $menuArray = [];
        foreach ($menu as $m) {
            
        
            if ($m->sub()->exists()) {
                foreach ($m->sub()->where('status','1')->oldest('order')->get() as $msub) {
                   if($msub->sub()->exists()){
                        foreach($msub->sub()->where('status','1')->oldest('order')->get() as $msub2){
                            if($msub2->sub()->exists()){

                            }else{
                                $menuRow = [
                                    'name' => $msub2->name,
                                    'permissions' => []
                                ];
                            
                                foreach ($user as $row) {
                                    $permissions = [
                                        'view' => $row->checkMenu($msub2->id,'view') ? 'V' : '-',
                                        'update' => $row->checkMenu($msub2->id,'update') ? 'V' : '-',
                                        'delete' => $row->checkMenu($msub2->id,'delete') ? 'V' : '-',
                                        'void' => $row->checkMenu($msub2->id,'void') ? 'V' : '-',
                                        'journal' => $row->checkMenu($msub2->id,'journal') ? 'V' : '-',
                                    ];
                            
                                    $menuRow['permissions'][] = $permissions;
                                }
                            
                                $menuArray[] = $menuRow;    
                            }
                        }
                   }else{
                        $menuRow = [
                            'name' => $msub->name,
                            'permissions' => []
                        ];
                    
                        foreach ($user as $row) {
                            $permissions = [
                                'view' => $row->checkMenu($msub->id,'view') ? 'V' : '-',
                                'update' => $row->checkMenu($msub->id,'update') ? 'V' : '-',
                                'delete' => $row->checkMenu($msub->id,'delete') ? 'V' : '-',
                                'void' => $row->checkMenu($msub->id,'void') ? 'V' : '-',
                                'journal' => $row->checkMenu($msub->id,'journal') ? 'V' : '-',
                            ];
                    
                            $menuRow['permissions'][] = $permissions;
                        }
                    
                        $menuArray[] = $menuRow;
                   }
                }
            } else {
                $menuRow = [
                    'name' => $m->name,
                    'permissions' => []
                ];
            
                foreach ($user as $row) {
                    $permissions = [
                        'view' => $row->checkMenu($m->id,'view') ? 'V' : '-',
                        'update' => $row->checkMenu($m->id,'update') ? 'V' : '-',
                        'delete' => $row->checkMenu($m->id,'delete') ? 'V' : '-',
                        'void' => $row->checkMenu($m->id,'void') ? 'V' : '-',
                        'journal' => $row->checkMenu($m->id,'journal') ? 'V' : '-',
                    ];
            
                    $menuRow['permissions'][] = $permissions;
                }
            
                $menuArray[] = $menuRow;
            }
        }
        $place = Place::where('status','1')->orderBy('code')->get();
        $warehouse = Warehouse::where('status','1')->orderBy('name')->get();
        function unique_key($array,$keyname){

            $new_array = array();
            foreach($array as $key=>$value){
            
                if(!isset($new_array[$value[$keyname]])){
                $new_array[$value[$keyname]] = $value;
                }
            
            }
            $new_array = array_values($new_array);
            return $new_array;
        }
        activity()
            ->performedOn(new User())
            ->causedBy(session('bo_id'))
            ->withProperties($user)
            ->log('Export Data Access data.');
        $data_array = unique_key($menuArray,'name');
        return view('admin.exports.data_access', [
            'user'      => $user,
            'menu'      => $menu,
            'menu_user' => $data_array,
            'place'     => $place,
            'warehouse' => $warehouse,
        ]);
    }
}
