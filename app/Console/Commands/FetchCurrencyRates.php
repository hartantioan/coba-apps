<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Currency;
use App\Models\CurrencyDate;

class FetchCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-currency-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $dateString = now()->toDateString();
        
        $query_currency = Currency::where('status',1)->get();
        foreach($query_currency as $row_currency){
            $response = Http::get("https://api.vatcomply.com/rates", [
                'base' => $row_currency->code,
                'date' => $dateString,
            ]);

            /* if ($response->successful()) { */
                $data = $response->json();
                /* $query_currency_ada = CurrencyDate::where('id',$row_currency->id)
                ->where('currency_date',$data['date'])
                ->get();
                if(count($query_currency_ada) == 0){ */
                  
                    CurrencyDate::create([
                        'currency_id'   => $row_currency->id,
                        'currency_date' => $data['date'],
                        'currency_rate' => $data['rates']['IDR'],
                        'taken_from'    => 'https://api.vatcomply.com/rates',
                    ]);
                /* }
                
    
                $this->info('Currency rates fetched and stored successfully.');
            } else {
                $this->error('Failed to fetch currency rates.');
            } */
        }
        
    }
}
