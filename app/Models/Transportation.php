<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Transportation extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'transportations';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'category_transportation',
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
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

    public function categoryTransportation(){
        $category_transportation = match ($this->category_transportation) {
          '1' => 'TRONTON',
          '2' => 'COLT DIESEL',
          default => '-',
        };

        return $category_transportation;
    }
}