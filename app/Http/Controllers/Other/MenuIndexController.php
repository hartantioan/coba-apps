<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuIndexController extends Controller
{
    public function index()
    {
        //cari semua menu
        $menu = Menu::where('status','1')->get();

        //temp arr
        $arrResult = [];

        //masukkan collection ke temp arr
        foreach($menu as $row){
            $arrResult[] = [
                'id'        => $row->id,
                'order'     => $row->order,
                'name'      => $row->name,
                'icon'      => $row->icon,
                'is_maintenance' => $row->is_maintenance ? $row->is_maintenance:'0',
                'is_new'    => $row->is_new ? $row->is_new:'0',
                'url'       => $row->url,
                'parent_id' => $row->parent_id ? $row->parent_id : 0,
            ];
        }

        //arr hasil
        $res = [];

        //loop temp arr dan proses rekursif disini
        foreach($arrResult as $e){
            $this->addToArr($res, $e);
        }

        //urutkan parent paling atas berdasarkan key index order
        $key_values = array_column($res, 'order'); 
        array_multisort($key_values, SORT_ASC, $res);

        $data = [
            'title' => 'ALL MENU',
            'content' => 'admin.other.menu',
            'menu' => $res,
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    function addToArr(&$arr, $data){
        if ($data['parent_id'] == 0){
            return $arr[] =  [
                'id'        => $data['id'], 
                'order'     => $data['order'], 
                'name'      => $data['name'], 
                'parent_id' => $data['parent_id'],
                'is_maintenance' => $data['is_maintenance'],
                'is_new'    => $data['is_new'],
                'icon'    =>$data['icon'],
                'url' => $data['url'],
                'child'  => []
            ];
        }
        foreach($arr as &$e) {
            if ($e['id'] == $data['parent_id']) {
                $e['child'][] = [
                    'id'        => $data['id'], 
                    'order'     => $data['order'],
                    'name'      => $data['name'],
                    'icon'    =>$data['icon'],
                    'is_maintenance' => $data['is_maintenance'],
                    'is_new'    => $data['is_new'],
                    'url' => $e['url'].'/'.$data['url'],
                    'parent_id' => $data['parent_id'], 
                    'child'  => []
                ];
                break;
            }
            $key_values = array_column($e['child'], 'order'); 
            array_multisort($key_values, SORT_ASC, $e['child']);
            $this->addToArr($e['child'], $data);
        }
    }
}
