<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class PersonalVisit extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'personal_visits';

    // The primary key associated with the table.
    protected $primaryKey = 'id';

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'image_in',
        'note_in',
        'image_out',
        'note_out',
        'date_in',
        'date_out',
        'location',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function attachmentIn()
    {
        if($this->image_in !== NULL && Storage::exists($this->image_in)) {
            $document_po = asset(Storage::url($this->image_in));
        } else {
            $document_po = asset('website/empty.jpg');
        }

        return $document_po;
    }

    public function attachmentOut()
    {
        if($this->image_out !== NULL && Storage::exists($this->image_out)) {
            $document_po = asset(Storage::url($this->image_out));
        } else {
            $document_po = asset('website/empty.jpg');
        }

        return $document_po;
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = PersonalVisit::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('code')
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
          '1' => '<span class="amber medium-small white-text padding-3">Start Visit</span>',
          '2' => '<span class="cyan medium-small white-text padding-3">Done Visit</span>',
          '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
          '6' => '<span class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

}
