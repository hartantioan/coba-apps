<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ClosingJournalDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'closing_journal_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'closing_journal_id',
        'coa_id',
        'type',
        'nominal',
    ];

    public function closingJournal()
    {
        return $this->belongsTo('App\Models\ClosingJournal', 'closing_journal_id', 'id')->withTrashed();
    }

    public function coa()
    {
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
