<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ChecklistDocumentList extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'checklist_document_lists';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'checklist_document_id',
        'lookable_type',
        'lookable_id',
        'value',
        'note',
    ];

    public function checklistDocument(){
        return $this->belongsTo('App\Models\ChecklistDocument', 'checklist_document_id', 'id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }
}