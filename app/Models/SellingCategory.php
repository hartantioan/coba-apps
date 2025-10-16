<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellingCategory extends Model
{
    protected $table = 'selling_categories';
    protected $fillable = [
        'name',
        'service_charge',
        'status',
    ];

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
}
