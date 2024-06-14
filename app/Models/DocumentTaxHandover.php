<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTaxHandover extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'document_tax_handovers';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'user_id',
        'code',
        'company_id',
        'place_id',
        'account_id',
        'post_date',
        'note',
        'void_id',
        'void_note',
        'void_date',
        'done_id',
        'done_note',
        'done_date',
        'delete_note',
        'delete_id',
        'status',
        
    ];

    public function documentTaxHandoverDetail()
    {
        return $this->hasMany('App\Models\DocumentTaxHandoverDetail');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id');
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = DocumentTaxHandover::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return substr($prefix,0,9).'-'.$no;
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Pending</span>',
          '2' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Approved</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
