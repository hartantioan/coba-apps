<?php

namespace App\Http\Controllers\Finance;

use App\Exports\ExportGoodReceiptFinance;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FinanceReportController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));
        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);
        $menu = Menu::where('url', $parentSegment)->first();
        $data = [
            'title'     => 'Finance Report',
            'content'   => 'admin.finance.report',
            'menus'     => Menu::where('parent_id',$menu->id)
                            ->whereHas('menuUser', function ($query) {
                                $query->where('user_id', session('bo_id'))
                                    ->where('type','view');
                            })
                            ->orderBy('order')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function exportGoodReceipt(Request $request){
        $menu = Menu::where('url','good_receipt_po')->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','report')->first();
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
        $modedata = $menuUser->mode ?? '';
        $nominal = $menuUser->show_nominal ?? '';
		return Excel::download(new ExportGoodReceiptFinance($post_date,$end_date,$mode,$modedata,$nominal,$this->datawarehouses), 'good_receipt_'.uniqid().'.xlsx');
    }
}
