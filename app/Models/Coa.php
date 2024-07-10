<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Illuminate\Support\Facades\DB;

class Coa extends Model
{
    use HasFactory, SoftDeletes, Notifiable, HasRecursiveRelationships;

    protected $table = 'coas';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'prefix',
        'name',
        'company_id',
        'parent_id',
        'currency_id',
        'level',
        'status',
        'is_cash_account',
        'is_hidden',
        'show_journal',
        'bp_journal',
        'kode_program_lama',
    ];

    public function company(){
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function parentSub(){
        return $this->belongsTo('App\Models\Coa', 'parent_id', 'id')->withTrashed();
    }

    public function currency(){
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id')->withTrashed();
    }

    public function childSub(){
        return $this->hasMany('App\Models\Coa', 'parent_id', 'id');
    }

    public function child(){
        $query = Coa::where('parent_id',$this->id)->orderBy('code')->get();
        return $query;
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function balancePaymentRequest(){
        return 0;
    }

    public function journalDebit(){
        return $this->hasMany('App\Models\JournalDetail','coa_id','id')->where('type','1')->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','coa_id','id')->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function journalCredit(){
        return $this->hasMany('App\Models\JournalDetail','coa_id','id')->where('type','2')->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function getTotalMonthFromParent($month,$level){
        if($level == '1'){
            $child = $this->getFifthChildFromFirst();
        }elseif($level == '2'){
            $child = $this->getFifthChildFromSecond();
        }elseif($level == '3'){
            $child = $this->getFifthChildFromThird();
        }elseif($level == '4'){
            $child = $this->getFifthChildFromFourth();
        }elseif($level == '5'){
            $child = $this->getFifthChildFromFifth();
        }
        
        $totalBalanceBeforeDebit = 0;
        $totalBalanceBeforeCredit = 0;
        $totalDebit = 0;
        $totalCredit = 0;

        $date = $month.'-01';

        foreach($child as $row){
            $dataBalanceBeforeDebit = NULL;
            $dataBalanceBeforeCredit = NULL;
            $dataDebit = NULL;
            $dataCredit = NULL;

            $dataBalanceBeforeDebit = DB::select("
                SELECT 
                    IFNULL(SUM(ROUND(nominal,2)),0) AS total
                FROM journal_details jd
                JOIN journals j
                    ON jd.journal_id = j.id
                WHERE 
                    jd.coa_id = :coa_id 
                    AND jd.deleted_at IS NULL
                    AND j.deleted_at IS NULL
                    AND j.post_date < :date
                    AND jd.type = '1'
            ", array(
                'coa_id'    => $row->id,
                'date'      => $date,
            ));

            $dataBalanceBeforeCredit = DB::select("
                SELECT 
                    IFNULL(SUM(ROUND(nominal,2)),0) AS total
                FROM journal_details jd
                JOIN journals j
                    ON jd.journal_id = j.id
                WHERE 
                    jd.coa_id = :coa_id 
                    AND jd.deleted_at IS NULL
                    AND j.deleted_at IS NULL
                    AND j.post_date < :date
                    AND jd.type = '2'
            ", array(
                'coa_id'    => $row->id,
                'date'      => $date,
            ));
            
            $totalBalanceBeforeDebit += $dataBalanceBeforeDebit[0]->total;

            $totalBalanceBeforeCredit += $dataBalanceBeforeCredit[0]->total;

            $dataDebit = $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])->whereRaw("post_date LIKE '$month%'");
            })->get();
    
            $dataCredit = $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])->whereRaw("post_date LIKE '$month%'");
            })->get();

            foreach($dataDebit as $rownow){
                $totalDebit += round($rownow->nominal,2);
            }

            foreach($dataCredit as $rownow2){
                $totalCredit += round($rownow2->nominal,2);
            }
        }

        $arr = [
            'totalBalanceBefore'    => $totalBalanceBeforeDebit - $totalBalanceBeforeCredit,
            'totalDebit'            => $totalDebit,
            'totalCredit'           => $totalCredit,
            'totalBalance'          => $totalDebit - $totalCredit,
        ];

        return $arr;
    }

    public function getTotalMonthFromParentExceptClosing($month,$level){
        if($level == '1'){
            $child = $this->getFifthChildFromFirst();
        }elseif($level == '2'){
            $child = $this->getFifthChildFromSecond();
        }elseif($level == '3'){
            $child = $this->getFifthChildFromThird();
        }elseif($level == '4'){
            $child = $this->getFifthChildFromFourth();
        }elseif($level == '5'){
            $child = $this->getFifthChildFromFifth();
        }

        $totalBalanceBeforeDebit = 0;
        $totalBalanceBeforeCredit = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        foreach($child as $row){
            $dataBalanceBeforeDebit = $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date < '$month-01'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->get();

            $dataBalanceBeforeCredit = $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date < '$month-01'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->get();

            foreach($dataBalanceBeforeDebit as $row){
                $totalBalanceBeforeDebit += round($row->nominal,2);
            }

            foreach($dataBalanceBeforeCredit as $row){
                $totalBalanceBeforeCredit += round($row->nominal,2);
            }

            $dataDebit = $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date LIKE '$month%'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->get();

            $dataCredit = $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date LIKE '$month%'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->get();

            foreach($dataDebit as $row){
                $totalDebit += round($row->nominal,2);
            }

            foreach($dataCredit as $row){
                $totalCredit += round($row->nominal,2);
            }
        }

        $arr = [
            'totalBalanceBefore'    => $totalBalanceBeforeDebit - $totalBalanceBeforeCredit,
            'totalDebit'            => $totalDebit,
            'totalCredit'           => $totalCredit,
            'totalBalance'          => $totalDebit - $totalCredit,
        ];

        return $arr;
    }

    public function getTotalMonthFromParentExceptClosingBefore($month,$level){
        if($level == '1'){
            $child = $this->getFifthChildFromFirst();
        }elseif($level == '2'){
            $child = $this->getFifthChildFromSecond();
        }elseif($level == '3'){
            $child = $this->getFifthChildFromThird();
        }elseif($level == '4'){
            $child = $this->getFifthChildFromFourth();
        }elseif($level == '5'){
            $child = $this->getFifthChildFromFifth();
        }

        $totalBalanceBeforeDebit = 0;
        $totalBalanceBeforeCredit = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        foreach($child as $row){
            $dataBalanceBeforeDebit = $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date < '$month-01'");
            })->get();
            
            foreach($dataBalanceBeforeDebit as $rowbefore){
                $totalBalanceBeforeDebit += round($rowbefore->nominal,2);
            }

            $dataBalanceBeforeCredit = $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date < '$month-01'");
            })->get();

            foreach($dataBalanceBeforeCredit as $rowbefore){
                $totalBalanceBeforeCredit += round($rowbefore->nominal,2);
            }

            $dataDebit = $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date LIKE '$month%'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->get();

            $dataCredit = $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date LIKE '$month%'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->get();

            foreach($dataDebit as $rownow){
                $totalDebit += round($rownow->nominal,2);
            }

            foreach($dataCredit as $rownow){
                $totalCredit += round($rownow->nominal,2);
            }
        }

        $arr = [
            'totalBalanceBefore'    => $totalBalanceBeforeDebit - $totalBalanceBeforeCredit,
            'totalDebit'            => $totalDebit,
            'totalCredit'           => $totalCredit,
            'totalBalance'          => $totalDebit - $totalCredit,
        ];

        return $arr;
    }

    public function getFifthChildFromFirst(){
        $arr = [];

        foreach($this->childSub as $row2){
            foreach($row2->childSub as $row3){
                foreach($row3->childSub as $row4){
                    foreach($row4->childSub as $row5){
                        $arr[] = $row5;
                    }
                }
            }
        }

        return $arr;
    }

    public function getFifthChildFromSecond(){
        $arr = [];

        foreach($this->childSub as $row3){
            foreach($row3->childSub as $row4){
                foreach($row4->childSub as $row5){
                    $arr[] = $row5;
                }
            }
        }

        return $arr;
    }

    public function getFifthChildFromThird(){
        $arr = [];

        foreach($this->childSub as $row4){
            foreach($row4->childSub as $row5){
                $arr[] = $row5;
            }
        }

        return $arr;
    }

    public function getFifthChildFromFourth(){
        $arr = [];

        foreach($this->childSub as $row4){
            $arr[] = $row4;
        }

        return $arr;
    }

    public function getFifthChildFromFifth(){
        $arr[] = $this;

        return $arr;
    }

    public function getBalanceFromDate($date){
        $totalDebit = 0;
        $totalCredit = 0;

        $dataDebit = $this->journalDebit()->whereHas('journal',function($query)use($date){
            $query->whereDate('post_date','<',$date);
        })->get();

        $dataCredit = $this->journalCredit()->whereHas('journal',function($query)use($date){
            $query->whereDate('post_date','<',$date);
        })->get();

        foreach($dataDebit as $row){
            $totalDebit += round($row->nominal,2);
        }

        foreach($dataCredit as $row){
            $totalCredit += round($row->nominal,2);
        }

        return $totalDebit - $totalCredit;
    }
}
