<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'taxes';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'coa_id',
        'type',
        'percentage',
        'is_default_ppn',
        'is_default_pph',
        'status'
    ];

    public function coa(){
        return $this->belongsTo('App\Models\Coa','coa_id','id')->withTrashed();
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public function isDefaultPpn(){
        $default = match ($this->is_default_ppn) {
          '1' => '<i class="material-icons" style="font-size: inherit !important;color:red;">star</i>',
          '0' => '',
          default => 'Invalid',
        };

        return $default;
    }

    public function isDefaultPph(){
        $default = match ($this->is_default_pph) {
          '1' => '<i class="material-icons" style="font-size: inherit !important;color:red;">star</i>',
          '0' => '',
          default => 'Invalid',
        };

        return $default;
    }

    public function type(){
        switch($this->type) {
            case '+':
                $type = 'Penambah';
                break;
            case '-':
                $type = 'Pengurang';
                break;
            default:
                $type = 'Invalid';
                break;
        }

        return $type;
    }
}