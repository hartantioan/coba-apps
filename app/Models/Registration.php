<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Registration extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'registrations';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'username',
        'password',
        'address',
        'email',
        'hp',
        'document',
        'status',
        'add_to_user'
    ];

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Menunggu</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Ditolak</span>',
          '3' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Disetujui</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function statusRaw(){
        $status = match ($this->status) {
          '1'   => 'Menunggu',
          '2'   => 'Ditolak',
          '3'   => 'Disetujui',
          default => 'Invalid',
        };

        return $status;
    }

    public function attachment() 
    {
        if($this->document !== NULL && Storage::exists($this->document)) {
            $document = asset(Storage::url($this->document));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->hasOne('App\Models\User');
    }
}
