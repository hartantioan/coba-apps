<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportCoa;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;

class JournalController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Jurnal',
            'content'   => 'admin.accounting.journal',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
}