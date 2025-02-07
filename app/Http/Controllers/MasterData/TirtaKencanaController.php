<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TirtaKencanaController extends Controller{

    private $API_TOKEN = "1|7onnoJ6JyH3RHAoaz3vjB5Jt2c7oHBvSUV4nzj8vf143690d";
    // private $API_TOKEN = env('API_TOKEN');
    private $baseUrl = "https://mitra.tirtakencana.com/blesscon-stage";

    public function getItemIndex(){
        $endpoint = "/api/items?offset=0&limit=100";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->API_TOKEN,
            'Accept'        => 'application/json', // untuk handling(menghindari) => jika gagal API return null
        ])->get($this->baseUrl.$endpoint);
        
        Log::info($response->status()." ---- ".$response->body());
        if(json_decode($response->body())->success){
            Log::info(json_decode($response->body())->success."----".json_decode($response->body())->message);
        }
        else{
            Log::info(json_decode($response->body())->success."----".json_decode($response->body())->messages);
        }

        if ($response->successful()){
            return $response->json();
        } else {
            return response()->json(['error' => $response->body()], $response->status());
        }
    }

    public function getItemShow($code="ITEM-1"){
        //get request
        // $code = "1.01.01.0001.4.C.REX";

        $endpoint = "/api/items/".base64_encode($code);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->API_TOKEN,
            'Accept'        => 'application/json',
        ])->get($this->baseUrl.$endpoint);

        if ($response->successful()){
            return $response->json();
        } else {
            return response()->json(['error' => $response->body()], $response->status());
        }
    }

    public function putItemUpdate($request){
        //get request
        $data = $request;
        $endpoint = "/api/items/".base64_encode($data['code']);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->API_TOKEN,
            'Accept'        => 'application/json',
        ])->put($this->baseUrl.$endpoint, $data);
        Log::info($response);
        if ($response->successful()){
            return $response->json();
        } else {
            return response()->json(['error' => $response->body()], $response->status());
        }
    }

    public function postItemStore($request){
        $data = $request;
        $endpoint = "/api/items/";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->API_TOKEN,
            'Accept'        => 'application/json',
        ])->post($this->baseUrl.$endpoint, $data);
        Log::info($response);
        if ($response->successful()){
            return $response->json();
        } else {
            return response()->json(['error' => $response->body()], $response->status());
        }
    }

    public function postPriceListStore($request){
        $data = $request;
        Log::info($data);
        $endpoint = "/api/item-pricelists/";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->API_TOKEN,
            'Accept'        => 'application/json',
        ])->post($this->baseUrl.$endpoint, $data);
        Log::info($response->body());
        if ($response->successful()){
            return $response->json();
        } else {
            return response()->json(['error' => $response->body()], $response->status());
        }
    }
}


?>