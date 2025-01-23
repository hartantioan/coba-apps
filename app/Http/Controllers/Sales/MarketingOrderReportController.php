<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportMarketingRecapitulation;
use App\Exports\ExportMarketingRecapitulationCsv;
use App\Exports\ExportMarketingRecapitulationCsv2;
use App\Exports\ExportCsvFromFile;
use App\Http\Controllers\Controller;
use App\Imports\SalesCsvImport;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderDownPayment;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\CustomHelper;

class MarketingOrderReportController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct()
    {
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request)
    {
        $data = [
            'title'         => 'Rekapitulasi',
            'content'       => 'admin.sales.recapitulation',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filterByDate(Request $request)
    {
        $start_time = microtime(true);

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $mo = MarketingOrder::whereIn('status', ['2', '3'])
            ->whereDate('post_date', '>=', $start_date)
            ->whereDate('post_date', '<=', $end_date)->get();

        $newData = [];

        foreach ($mo as $row) {
            $totalInvoice = $row->totalInvoice();
            $totalMemo = $row->totalMemo();
            $totalPayment = $row->totalPayment();
            $balance = $totalInvoice - $totalMemo - $totalPayment;
            $newData[] = [
                'code'              => $row->code,
                'customer'          => $row->account->name,
                'post_date'         => date('d/m/Y', strtotime($row->post_date)),
                'top'               => $row->top_customer,
                'note'              => $row->note_internal . ' - ' . $row->note_external,
                'total'             => number_format($row->total, 2, ',', '.'),
                'tax'               => number_format($row->tax, 2, ',', '.'),
                'grandtotal'        => number_format($row->grandtotal, 2, ',', '.'),
                'schedule'          => number_format($row->totalMod(), 2, ',', '.'),
                'sent'              => number_format($row->totalModProcess(), 2, ',', '.'),
                'return'            => number_format($row->totalReturn(), 2, ',', '.'),
                'invoice'           => number_format($totalInvoice, 2, ',', '.'),
                'memo'              => number_format($totalMemo, 2, ',', '.'),
                'payment'           => number_format($totalPayment, 2, ',', '.'),
                'balance'           => number_format($balance, 2, ',', '.'),
            ];
        }

        $end_time = microtime(true);

        $execution_time = ($end_time - $start_time);

        $response = [
            'status'            => 200,
            'content'           => $newData,
            'execution_time'    => round($execution_time, 5),
        ];

        return response()->json($response);
    }

    public function export(Request $request)
    {
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportMarketingRecapitulation($request->start_date, $request->end_date), 'sales_recapitulation_' . uniqid() . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }

    public function exportCsv(Request $request)
    {
        /* return Excel::download(new ExportMarketingRecapitulationCsv2($request->start_date,$request->end_date), 'sales_csv_'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX); */
        return Excel::download(new ExportMarketingRecapitulationCsv($request->start_date, $request->end_date), 'sales_csv_' . uniqid() . '.csv', \Maatwebsite\Excel\Excel::CSV);
        /* $namefile = 'sales_csv_'.uniqid().'.xlsx';
        Excel::store(new ExportMarketingRecapitulationCsv2($request->start_date,$request->end_date), $namefile,'public', \Maatwebsite\Excel\Excel::XLSX);
        $import = Excel::toArray(new SalesCsvImport,storage_path('app/public/' . $namefile));
        return Excel::download(new ExportCsvFromFile($import), 'sales_csv_'.uniqid().'.csv', \Maatwebsite\Excel\Excel::CSV); */
    }

    public function exportXml(Request $request)
    {

        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->end_date ? $request->end_date : '';

        $ardp = MarketingOrderDownPayment::whereIn('status', ['2', '3'])
            ->whereDate('post_date', '>=', $start_date)
            ->whereDate('post_date', '<=', $finish_date)
            ->whereNotNull('tax_no')
            ->where('tax_no', '!=', '')
            
            ->get();


        $invoice = MarketingOrderInvoice::whereIn('status', ['2', '3'])
            ->whereDate('post_date', '>=', $start_date)
            ->whereDate('post_date', '<=', $finish_date)
            ->whereNotNull('tax_no')
            ->where('tax_no', '!=', '')
          
            // ->where('code', '=', 'ARIN-25P1-00000173')
            ->get();



        $dom = new \DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xml_file_name = 'storage/xml/FK.xml';
        $root = $dom->createElement('TaxInvoiceBulk');
        $attrroot1 = new \DOMAttr('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $root->setAttributeNode($attrroot1);
        $attrroot2 = new \DOMAttr('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNode($attrroot2);
        $TIN = $dom->createElement('TIN', '0608293056618000');
        $root->appendChild($TIN);
        $List = $dom->createElement('ListOfTaxInvoice');
        //header
        foreach ($ardp as $key => $row) {




            $TaxInvoice = $dom->createElement('TaxInvoice');

            $TaxInvoiceDate = $dom->createElement('TaxInvoiceDate', $row->post_date);
            $TaxInvoiceOpt = $dom->createElement('TaxInvoiceOpt', 'Normal');
            $TrxCode = $dom->createElement('TrxCode', '04');
            $AddInfo = $dom->createElement('AddInfo', '');
            $CustomDoc = $dom->createElement('CustomDoc', '');
            $RefDesc = $dom->createElement('RefDesc', $row->code);
            $FacilityStamp = $dom->createElement('FacilityStamp', '');
            $SellerIDTKU = $dom->createElement('SellerIDTKU', '0608293056618000000000');
            $BuyerTin = $dom->createElement('BuyerTin', $row->getNpwpCoreTax());
            $BuyerDocument = $dom->createElement('BuyerDocument', $row->getBuyerDocCoreTax());
            $BuyerCountry = $dom->createElement('BuyerCountry', 'IDN');
            $BuyerDocumentNumber = $dom->createElement('BuyerDocumentNumber',  $row->getBuyerDocNumberCoreTax());
            $BuyerName = $dom->createElement('BuyerName', $row->account->userDataDefault()->title);
            $BuyerAdress = $dom->createElement('BuyerAdress', $row->account->userDataDefault()->address);
            $BuyerEmail = $dom->createElement('BuyerEmail', '');
            $BuyerIDTKU = $dom->createElement('BuyerIDTKU', $row->getNitkuCoreTax() );
            //header
            $ListOfGoodService = $dom->createElement('ListOfGoodService');

            $TaxInvoice->appendChild($TaxInvoiceDate);
            $TaxInvoice->appendChild($TaxInvoiceOpt);
            $TaxInvoice->appendChild($TrxCode);
            $TaxInvoice->appendChild($AddInfo);
            $TaxInvoice->appendChild($CustomDoc);
            $TaxInvoice->appendChild($RefDesc);
            $TaxInvoice->appendChild($FacilityStamp);
            $TaxInvoice->appendChild($SellerIDTKU);
            $TaxInvoice->appendChild($BuyerTin);
            $TaxInvoice->appendChild($BuyerDocument);
            $TaxInvoice->appendChild($BuyerCountry);
            $TaxInvoice->appendChild($BuyerDocumentNumber);
            $TaxInvoice->appendChild($BuyerName);
            $TaxInvoice->appendChild($BuyerAdress);
            $TaxInvoice->appendChild($BuyerEmail);
            $TaxInvoice->appendChild($BuyerIDTKU);
            $TaxInvoice->appendChild($ListOfGoodService);
          
            

              

                $GoodService = $dom->createElement('GoodService');
                $ListOfGoodService->appendChild($GoodService);
                //detail
                $Opt = $dom->createElement('Opt', 'A');
                $Code = $dom->createElement('Code', '000000');
                $Name = $dom->createElement('Name',   $row->note);
                $Unit = $dom->createElement('Unit', 'UM.0033');
                $Price = $dom->createElement('Price', round($row->total, 2));
                $Qty = $dom->createElement('Qty', 1);
                $TotalDiscount = $dom->createElement('TotalDiscount',0);
                $TaxBase = $dom->createElement('TaxBase', round($row->total, 2));
                $OtherTaxBase = $dom->createElement('OtherTaxBase', round(11 / 12 * ( round($row->total, 2)), 2));
                $VATRate = $dom->createElement('VATRate', '12');
                //$VAT = $dom->createElement('VAT', $tax);
                $VAT = $dom->createElement('VAT', Round((round($row->total, 2)) * 0.11, 2));
                $STLGRate = $dom->createElement('STLGRate', '0');
                $STLG = $dom->createElement('STLG', '0');
                //detail
                $GoodService->appendChild($Opt);
                $GoodService->appendChild($Code);
                $GoodService->appendChild($Name);
                $GoodService->appendChild($Unit);
                $GoodService->appendChild($Price);
                $GoodService->appendChild($Qty);
                $GoodService->appendChild($TotalDiscount);
                $GoodService->appendChild($TaxBase);
                //$GoodService->appendChild($TaxBase);
                $GoodService->appendChild($OtherTaxBase);
                $GoodService->appendChild($VATRate);
                $GoodService->appendChild($VAT);
                $GoodService->appendChild($STLGRate);
                $GoodService->appendChild($STLG);
               
          
            $List->appendChild($TaxInvoice);


            $TaxInvoice = $dom->createElement('TaxInvoice', '');

            $root->appendChild($List);
        }

        foreach ($invoice as $key => $row) {




            $TaxInvoice = $dom->createElement('TaxInvoice');
            $freeAreaTax = $row->marketingOrderDeliveryProcess()->exists() ? ($row->marketingOrderDeliveryProcess->marketingOrderDelivery->getMaxTaxType() == '2' ? '18' : '') : '';

            $TaxInvoiceDate = $dom->createElement('TaxInvoiceDate', $row->post_date);
            $TaxInvoiceOpt = $dom->createElement('TaxInvoiceOpt', 'Normal');
            $TrxCode = $dom->createElement('TrxCode', '04');
            $AddInfo = $dom->createElement('AddInfo', '');
            $CustomDoc = $dom->createElement('CustomDoc', '');
            $RefDesc = $dom->createElement('RefDesc', $row->code);
            $FacilityStamp = $dom->createElement('FacilityStamp', '');
            $SellerIDTKU = $dom->createElement('SellerIDTKU', '0608293056618000000000');
            $BuyerTin = $dom->createElement('BuyerTin', $row->getNpwpCoreTax());
            $BuyerDocument = $dom->createElement('BuyerDocument', $row->getBuyerDocCoreTax());
            $BuyerCountry = $dom->createElement('BuyerCountry', 'IDN');
            $BuyerDocumentNumber = $dom->createElement('BuyerDocumentNumber', $row->getBuyerDocNumberCoreTax());
            $BuyerName = $dom->createElement('BuyerName', $row->userData->title);
            $BuyerAdress = $dom->createElement('BuyerAdress', $row->userData->address);
            $BuyerEmail = $dom->createElement('BuyerEmail', '');
            $BuyerIDTKU = $dom->createElement('BuyerIDTKU', $row->getNitkuCoreTax());
            //header
            $ListOfGoodService = $dom->createElement('ListOfGoodService');

            $TaxInvoice->appendChild($TaxInvoiceDate);
            $TaxInvoice->appendChild($TaxInvoiceOpt);
            $TaxInvoice->appendChild($TrxCode);
            $TaxInvoice->appendChild($AddInfo);
            $TaxInvoice->appendChild($CustomDoc);
            $TaxInvoice->appendChild($RefDesc);
            $TaxInvoice->appendChild($FacilityStamp);
            $TaxInvoice->appendChild($SellerIDTKU);
            $TaxInvoice->appendChild($BuyerTin);
            $TaxInvoice->appendChild($BuyerDocument);
            $TaxInvoice->appendChild($BuyerCountry);
            $TaxInvoice->appendChild($BuyerDocumentNumber);
            $TaxInvoice->appendChild($BuyerName);
            $TaxInvoice->appendChild($BuyerAdress);
            $TaxInvoice->appendChild($BuyerEmail);
            $TaxInvoice->appendChild($BuyerIDTKU);
            $TaxInvoice->appendChild($ListOfGoodService);
            $balance = floor($row->tax);
            foreach ($row->marketingOrderInvoiceDetail()->whereNull('lookable_type')->get() as $key => $rowdetail) {
                $price = $rowdetail->priceBeforeTax();
                $totalBeforeTax = round($rowdetail->totalBeforeTax(), 2);
                $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);

                
                $GoodService = $dom->createElement('GoodService');
                $ListOfGoodService->appendChild($GoodService);
                //detail
                $Opt = $dom->createElement('Opt', 'A');
                $Code = $dom->createElement('Code', '000000');
                $Name = $dom->createElement('Name',   $rowdetail->description);
                $Unit = $dom->createElement('Unit', 'UM.0033');
                $Price = $dom->createElement('Price', round($price, 2));
                $Qty = $dom->createElement('Qty', round($rowdetail->qty, 2));
                $TotalDiscount = $dom->createElement('TotalDiscount', $totalDiscountBeforeTax);
                $TaxBase = $dom->createElement('TaxBase', round($rowdetail->total, 2));
                $OtherTaxBase = $dom->createElement('OtherTaxBase', round(11 / 12 * (round($rowdetail->total, 2)), 2));
                $VATRate = $dom->createElement('VATRate', '12');
                //$VAT = $dom->createElement('VAT', $tax);
                $VAT = $dom->createElement('VAT', round($rowdetail->tax,2));
                $STLGRate = $dom->createElement('STLGRate', '0');
                $STLG = $dom->createElement('STLG', '0');
                //detail
                $GoodService->appendChild($Opt);
                $GoodService->appendChild($Code);
                $GoodService->appendChild($Name);
                $GoodService->appendChild($Unit);
                $GoodService->appendChild($Price);
                $GoodService->appendChild($Qty);
                $GoodService->appendChild($TotalDiscount);
                $GoodService->appendChild($TaxBase);
                //$GoodService->appendChild($TaxBase);
                $GoodService->appendChild($OtherTaxBase);
                $GoodService->appendChild($VATRate);
                $GoodService->appendChild($VAT);
                $GoodService->appendChild($STLGRate);
                $GoodService->appendChild($STLG);
                
            }

            foreach ($row->marketingOrderInvoiceDetail()->where('lookable_type', 'marketing_order_delivery_details')->get() as $key => $rowdetail) {
                if ($key == ($row->marketingOrderInvoiceDetail()->count() - 1)) {
                    $tax = $balance;
                } else {
                    $tax = $rowdetail->proportionalTaxFromHeader();
                }

                $hscode = '';
                if ($freeAreaTax) {
                    $hscode = ' ' . $rowdetail->lookable->item->type->hs_code;
                }
                $boxQty = '';

                $boxQty = ' ( ' . CustomHelper::formatConditionalQty($rowdetail->qty * $rowdetail->lookable->item->pallet->box_conversion) . ' BOX )';
                $price = round($rowdetail->priceBeforeTax(), 2);
                $qty = round($rowdetail->qty * $rowdetail->lookable->marketingOrderDetail->qty_conversion, 2);
                $totalBeforeTax = round($price * $qty, 2);
                $totalDiscountBeforeTax = round($rowdetail->totalDiscountBeforeTax(), 2);

                $GoodService = $dom->createElement('GoodService');
                $ListOfGoodService->appendChild($GoodService);
                //detail
                $Opt = $dom->createElement('Opt', 'A');
                $Code = $dom->createElement('Code', '000000');
                $Name = $dom->createElement('Name',   $rowdetail->lookable->item->print_name . $boxQty . $hscode);
                $Unit = $dom->createElement('Unit', 'UM.0012');
                $Price = $dom->createElement('Price', round($price, 2));
                $Qty = $dom->createElement('Qty', round($rowdetail->qty * $rowdetail->lookable->marketingOrderDetail->qty_conversion, 2));
                $TotalDiscount = $dom->createElement('TotalDiscount', $totalDiscountBeforeTax);
                $TaxBase = $dom->createElement('TaxBase', $totalBeforeTax - $totalDiscountBeforeTax);
                $OtherTaxBase = $dom->createElement('OtherTaxBase', round(11 / 12 * ($totalBeforeTax - $totalDiscountBeforeTax), 2));
                $VATRate = $dom->createElement('VATRate', '12');
                //$VAT = $dom->createElement('VAT', $tax);
                $VAT = $dom->createElement('VAT', Round(($totalBeforeTax - $totalDiscountBeforeTax) * 0.11, 2));
                $STLGRate = $dom->createElement('STLGRate', '0');
                $STLG = $dom->createElement('STLG', '0');
                //detail
                $GoodService->appendChild($Opt);
                $GoodService->appendChild($Code);
                $GoodService->appendChild($Name);
                $GoodService->appendChild($Unit);
                $GoodService->appendChild($Price);
                $GoodService->appendChild($Qty);
                $GoodService->appendChild($TotalDiscount);
                $GoodService->appendChild($TaxBase);
                //$GoodService->appendChild($TaxBase);
                $GoodService->appendChild($OtherTaxBase);
                $GoodService->appendChild($VATRate);
                $GoodService->appendChild($VAT);
                $GoodService->appendChild($STLGRate);
                $GoodService->appendChild($STLG);
                $balance -= $tax;
            }
            $List->appendChild($TaxInvoice);


            $TaxInvoice = $dom->createElement('TaxInvoice', '');/*
        $TaxInvoiceDate = $dom->createElement('TaxInvoiceDate', '2025-01-01');
        $TaxInvoiceOpt = $dom->createElement('TaxInvoiceOpt', 'Normal');
        $TrxCode = $dom->createElement('TrxCode', '04');
        $AddInfo = $dom->createElement('AddInfo', '');
        $CustomDoc = $dom->createElement('CustomDoc', '');
        $RefDesc = $dom->createElement('RefDesc', '');
        $FacilityStamp = $dom->createElement('FacilityStamp', '');
        $SellerIDTKU = $dom->createElement('SellerIDTKU', '0608293056618000000000');
        $BuyerTin = $dom->createElement('BuyerTin', '0314113663616000');
        $BuyerDocument = $dom->createElement('BuyerDocument', 'TIN');
        $BuyerCountry = $dom->createElement('BuyerCountry', 'IDN');
        $BuyerDocumentNumber = $dom->createElement('BuyerDocumentNumber', '-');
        $BuyerName = $dom->createElement('BuyerName', 'PT. SUPERIOR PRIMA SUKSES TBK');
        $BuyerAdress = $dom->createElement('BuyerAdress', 'Jl. Raya Kupang Baru No 27, Surabaya');
        $BuyerEmail = $dom->createElement('BuyerEmail', '');
        $BuyerIDTKU = $dom->createElement('BuyerIDTKU', '0314113663616000000000');
        //header
        $ListOfGoodService = $dom->createElement('ListOfGoodService');

        $TaxInvoice->appendChild($TaxInvoiceDate);
        $TaxInvoice->appendChild($TaxInvoiceOpt);
        $TaxInvoice->appendChild($TrxCode);
        $TaxInvoice->appendChild($AddInfo);
        $TaxInvoice->appendChild($CustomDoc);
        $TaxInvoice->appendChild($RefDesc);
        $TaxInvoice->appendChild($FacilityStamp);
        $TaxInvoice->appendChild($SellerIDTKU);
        $TaxInvoice->appendChild($BuyerTin);
        $TaxInvoice->appendChild($BuyerDocument);
        $TaxInvoice->appendChild($BuyerCountry);
        $TaxInvoice->appendChild($BuyerDocumentNumber);
        $TaxInvoice->appendChild($BuyerName);
        $TaxInvoice->appendChild($BuyerAdress);
        $TaxInvoice->appendChild($BuyerEmail);
        $TaxInvoice->appendChild($BuyerIDTKU);
        $TaxInvoice->appendChild($ListOfGoodService);
        $GoodService = $dom->createElement('GoodService');
        $ListOfGoodService->appendChild($GoodService);
        //detail
        $Opt = $dom->createElement('Opt', 'A');
        $Code = $dom->createElement('Code', '');
        $Name = $dom->createElement('Name', 'EOS CREMA 60X60 P48 EXP (960 BOX)');
        $Unit = $dom->createElement('Unit', 'UM.0012');
        $Price = $dom->createElement('Price', '54954.95');
        $Qty = $dom->createElement('Qty', '1382.4');
        $TotalDiscount = $dom->createElement('TotalDiscount', '151939.46');
        $TaxBase = $dom->createElement('TaxBase', '75817783.42');
        $OtherTaxBase = $dom->createElement('OtherTaxBase', '69499634.8');
        $VATRate = $dom->createElement('VATRate', '12');
        $VAT = $dom->createElement('VAT', '8339956.18');
        $STLGRate = $dom->createElement('STLGRate', '0');
        $STLG = $dom->createElement('STLG', '0');
        //detail
        $GoodService->appendChild($Opt);
        $GoodService->appendChild($Code);
        $GoodService->appendChild($Name);
        $GoodService->appendChild($Unit);
        $GoodService->appendChild($Price);
        $GoodService->appendChild($Qty);
        $GoodService->appendChild($TotalDiscount);
        $GoodService->appendChild($TaxBase);
        $GoodService->appendChild($TaxBase);
        $GoodService->appendChild($OtherTaxBase);
        $GoodService->appendChild($VATRate);
        $GoodService->appendChild($VAT);
        $GoodService->appendChild($STLGRate);
        $GoodService->appendChild($STLG);*/



            $root->appendChild($List);
        }
        // $root = $dom->createElement('Movies');
        /* $movie_node = $dom->createElement('movie');
        $attr_movie_id = new \DOMAttr('movie_id', '5467');
        $movie_node->setAttributeNode($attr_movie_id);

        $child_node_title = $dom->createElement('Title', 'The Campaign');
        $movie_node->appendChild($child_node_title);

        $child_node_year = $dom->createElement('Year', 2012);
        $movie_node->appendChild($child_node_year);

        $child_node_genre = $dom->createElement('Genre', 'The Campaign');
        $movie_node->appendChild($child_node_genre);

        $child_node_ratings = $dom->createElement('Ratings', 6.2);
        $movie_node->appendChild($child_node_ratings);
        $root->appendChild($movie_node);*/
        $dom->appendChild($root);
        // $root->appendChild($TIN);
        //$dom->appendChild($root);
        $dom->save($xml_file_name);
        header('Content-disposition: attachment; filename="' . $xml_file_name . '"');
        header('Content-type: "text/xml"; charset="utf8"');
        readfile($xml_file_name);
    }
}
