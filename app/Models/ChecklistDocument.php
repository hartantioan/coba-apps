<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ChecklistDocument extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'checklist_documents';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'menu_id',
        'title',
        'type',
        'is_other'
    ];

    public function type(){
        $type = match ($this->type) {
            '1' => 'Wajib',
            '2' => 'Bila Ada',
            '3' => 'Wajib Bila Jasa',
            '4' => 'Wajib Bila Pekerjaan Sipil',
            default => 'Invalid',
        };

        return $type;
    }

    public function isOther(){
        $is_other = match ($this->is_other) {
            '1' => 'Ya',
            default => 'Tidak',
        };

        return $is_other;
    }

    public function checklistDocumentList()
    {
        return $this->hasMany('App\Models\ChecklistDocumentList');
    }

    public function checkDocument($type,$id){
        $cek = $this->checklistDocumentList()->where('lookable_type',$type)->where('lookable_id',$id)->first();
        if($cek){
            return $cek;
        }else{
            return false;
        }
    }
}