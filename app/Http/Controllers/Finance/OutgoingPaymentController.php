<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Currency;
use App\Models\ItemCogs;
use App\Helpers\CustomHelper;
use App\Exports\ExportOutgoingPayment;
use App\Models\Place;
use Illuminate\Database\Eloquent\Builder;

class OutgoingPaymentController extends Controller
{

    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }
    public function index()
    {
        $data = [
            'title'         => 'Kas / Bank Keluar',
            'content'       => 'admin.finance.outgoing_payment',
            'currency'      => Currency::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
}