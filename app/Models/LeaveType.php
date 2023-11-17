<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'leave_types';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'type',
        'status',
        'shift_count',
        'furlough_type',
    ];

    public function leaveRequest(){
        return $this->hasMany('App\Models\LeaveRequest');
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            case '3':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            case '7':
                $status = '<span class="pink lighten-2 medium-small white-text padding-3">Melahirkan</span>';
                break;  
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public function type(){
        switch($this->type) {
            case '1':
                $type = 'Tanggal';
                break;
            case '2':
                $type = 'Jam & Tanggal';
                break;
            case '3':
                $type = 'Tanggal Range';
                break;
            case '4':
                $type = 'Tanggal & Multi Jam';
                break;
            case '7':
                $type = 'Melahirkan';
                break;
            default:
                $type = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $type;
    }

    public function furloughType(){
        switch($this->furlough_type) {
            case '1':
                $furlough_type = 'Cuti';
                break;
            case '2':
                $furlough_type = 'Sakit';
                break;
            case '3':
                $furlough_type = 'Cuti Khusus';
                break;
            case '4':
                $furlough_type = 'Dinas';
                break;
            case '5':
                $furlough_type = 'WFH';
                break;
            case '6':
                $furlough_type = 'Dispen';
                break;
            case '7':
                $furlough_type = 'Melahirkan';
                break;
            case '8':
                $furlough_type = 'Ijin';
                break;
            case '9':
                $furlough_type = 'Telat';
                break;
            default:
                $furlough_type = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $furlough_type;
    }

    
}
