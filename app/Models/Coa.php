<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

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
        foreach($child as $row){

            $totalBalanceBeforeDebit += $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])->whereRaw("post_date < '$month-01'");
            })->sum('nominal');

            $totalBalanceBeforeCredit += $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])->whereRaw("post_date < '$month-01'");
            })->sum('nominal');

            $totalDebit += $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])->whereRaw("post_date LIKE '$month%'");
            })->sum('nominal');
    
            $totalCredit += $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])->whereRaw("post_date LIKE '$month%'");
            })->sum('nominal');
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
            $totalBalanceBeforeDebit += $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date < '$month-01'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->sum('nominal');

            $totalBalanceBeforeCredit += $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date < '$month-01'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->sum('nominal');

            $totalDebit += $row->journalDebit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date LIKE '$month%'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->sum('nominal');

            $totalCredit += $row->journalCredit()->whereHas('journal',function($query)use($month){
                $query->whereIn('status',['2','3'])
                    ->whereRaw("post_date LIKE '$month%'")
                    ->where(function($query){
                        $query->where('lookable_type','!=','closing_journals')
                            ->orWhereNull('lookable_type');
                    });
            })->sum('nominal');
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
        $totalDebit = $this->journalDebit()->whereHas('journal',function($query)use($date){
            $query->whereDate('post_date','<',$date);
        })->sum('nominal');

        $totalCredit = $this->journalCredit()->whereHas('journal',function($query)use($date){
            $query->whereDate('post_date','<',$date);
        })->sum('nominal');

        return $totalDebit - $totalCredit;
    }
}
