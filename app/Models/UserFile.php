<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserFile extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'user_files';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'user_id',
        'file_name',
        'file_storage',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function fileLocation() 
    {
        if(Storage::exists($this->file_storage)) {
            if(explode('.',$this->file_storage)[1] == 'pdf'){
                $document = '<a data-magnify="gallery" data-src="" data-caption="'.$this->file_name.'" data-group="a" href="'.asset(Storage::url($this->file_storage)).'" target="_blank"><i class="material-icons" style="font-size:200px !important;">picture_as_pdf</i></a>';
            }else{ 
                $document = '<a data-magnify="gallery" data-src="" data-caption="'.$this->file_name.'" data-group="a" href="'.asset(Storage::url($this->file_storage)).'" target="_blank"><img src="'.asset(Storage::url($this->file_storage)).'" style="max-height:200px;" class="img-fluid img-thumbnail"></a>';
            }
        } else {
            $document = asset('website/empty_profile.png');
        }

        return $document;
    }

    public function deleteFile(){
		if(Storage::exists($this->file_storage)) {
            Storage::delete($this->file_storage);
        }
	}
}