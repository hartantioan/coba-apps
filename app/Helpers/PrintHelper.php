<?php

namespace App\Helpers;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class PrintHelper {
    public static function print($pr , $title = null , $size , $orientation , $blade ){
        $data = [
            'title'     => $title,
            'data'      => $pr
        ];
        $opciones_ssl=array(
            "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
            ),
        );
        CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
        $img_path = 'website/logo_web_fix.png';
        $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
        $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
        $img_base_64 = base64_encode($image_temp);
        $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
        $data["image"]=$path_img;
        $e_banking = 'website/payment_request_e_banking.jpeg';
        $extencion_banking = pathinfo($e_banking, PATHINFO_EXTENSION);
        $image_temp_banking = file_get_contents($e_banking);
        $img_base_64_banking = base64_encode($image_temp_banking);
        $path_img_banking = 'data:image/' . $extencion_banking . ';base64,' . $img_base_64_banking;
        $data["e_banking"]=$path_img_banking;
        $pdf = Pdf::loadView($blade, $data)->setPaper($size, $orientation);

        return $pdf;
    }
    public static function savePrint($content){
       
            
        $randomString = Str::random(10); 

        
        $filePath = 'public/pdf/' . $randomString . '.pdf';
        

        Storage::put($filePath, $content);
        
        $document_po = asset(Storage::url($filePath));
        $var_link=$document_po;

        return $var_link;
    }
}