<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

class Bom extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'boms';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'user_id',
        'item_id',
        'item_reject_id',
        'place_id',
        'qty_output',
        'is_powder',
        'group',
        'bom_standard_id',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }

    public function itemReject(){
        return $this->belongsTo('App\Models\Item', 'item_reject_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function bomStandard(){
        return $this->belongsTo('App\Models\BomStandard', 'bom_standard_id', 'id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine(){
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function bomDetail(){
        return $this->hasMany('App\Models\BomDetail');
    }

    public function bomAlternative(){
        return $this->hasMany('App\Models\BomAlternative');
    }

    public function bomParentMap(){
        return $this->hasMany('App\Models\BomMap','parent_id','id');
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

    public function isPowder(){
        $ispowder = match ($this->is_powder) {
            '1' => 'Ya',
            default => 'Tidak',
        };
  
          return $ispowder;
    }

    public function group(){
        $group = match ($this->group) {
            '1' => 'Powder',
            '2' => 'Green Tile',
            '3' => 'Finished Goods',
            default => 'Undefined',
        };
  
          return $group;
    }

    public function type(){
        switch($this->type) {
            case '1':
                $status = 'Perakitan';
                break;
            case '2':
                $status = 'Penjualan';
                break;
            case '3':
                $status = 'Produksi';
                break;
            case '4':
                $status = 'Template';
                break;
            default:
                $status = 'Invalid';
                break;
        }

        return $status;
    }

    public function getQtyContent($bomParent){
        $qty = 0;
        foreach($bomParent->bomDetail()->where('lookable_type','items')->where('lookable_id',$this->item_id)->get() as $row){
            $qty += $row->qty;
        }
        return $qty;
    }
}
