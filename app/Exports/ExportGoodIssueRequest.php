<?php

namespace App\Exports;

use App\Models\GoodIssueRequest;
use App\Models\MaterialRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportGoodIssueRequest implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode;

    public function __construct(string $start_date, string $end_date, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
    }

    public function view(): View
    {
        if($this->mode == '1'){
            $data = GoodIssueRequest::where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new GoodIssueRequest())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Good Issue  data.');
            return view('admin.exports.good_issue_request', [
                'data' => $data
            ]);
        }elseif($this->mode == '2'){
            $data = GoodIssueRequest::withTrashed()->where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new GoodIssueRequest())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Good Issue  data.');
            return view('admin.exports.good_issue_request', [
                'data' => $data
            ]);
        }
    }
}
