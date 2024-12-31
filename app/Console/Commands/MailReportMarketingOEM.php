<?php

namespace App\Console\Commands;


use App\Mail\SendMailMarketingOEM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportStockOEM;


class MailReportMarketingOEM extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'emailmarketingoem:run';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'All cron job and custom script goes here.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		//$recipient = ['henrianto@superior.co.id', 'hendra@superior.co.id', 'andrew@superior.co.id', 'haidong@superiorporcelain.co.id', 'billylaw@superior.co.id'];
		$recipient = ['iwan@superiorporcelain.co.id','haidong@superiorporcelain.co.id'];
		//  $akun = MarketingOrderInvoice::whereIn('status',[2])->distinct('account_id')->get('account_id');
		//$recipient = ['edp@superior.co.id'];
		// foreach ($akun as $pangsit) {
		$data = [];
		$data2 = [];
		$data3 = [];
		$data4 = [];
		$data5 = [];



		//oem 1a

		$aspglobal = 0.00;
		$aspglaze = 0.00;
		$aspht = 0.00;
		$aspgl = 0.00;

		//asp
		$query = DB::select("SELECT sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               ");

		foreach ($query as $row) {
			$aspglobal = $row->asp;
		}

		//asp ht
		$aspht = 0;
		$query = DB::select("SELECT sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
             and d.id=1  
			   ");

		foreach ($query as $row) {
			$aspht = $row->asp;
		}

		//asp glaze
		$query = DB::select("SELECT sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
             and d.id=2  
			   ");

		foreach ($query as $row) {
			$aspglaze = $row->asp;
		}

		$query = DB::select("
       	SELECT a.name, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp
					FROM (SELECT distinct concat(concat(b.name,' '),c.name) AS name FROM items a LEFT JOIN types b ON a.type_id=b.id
					LEFT JOIN varieties c ON c.id=a.variety_id
					WHERE is_sales_item=1 AND a.type_id IS NOT null )a LEFT JOIN (

					SELECT concat(concat(d.name,' '),e.name) AS tipe, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties e ON e.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY concat(CONCAT(d.name,' '),e.name))b ON a.name=b.tipe
               LEFT JOIN (

               SELECT concat(concat(d.name,' '),f.name) AS tipe, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id and e.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties f ON f.id=c.variety_id

					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY concat(concat(d.name,' '),f.name)
					)c ON c.tipe=a.name
					LEFT JOIN (
					SELECT concat(concat(d.name,' '),g.name) AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties g ON g.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY concat(concat(d.name,' '),g.name)
					)d ON d.tipe=a.name
					LEFT JOIN (
					SELECT concat(concat(e.name,' '),f.name) AS tipe,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
					LEFT JOIN types e ON e.id=d.type_id
					LEFT JOIN varieties f ON f.id=d.variety_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY concat(concat(e.name,' '),f.name)
					)e ON e.tipe=a.name
					LEFT JOIN
					(
					SELECT concat(concat(f.name,' '),g.name) AS tipe, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN items e ON e.id=b.item_id
					LEFT JOIN types f ON f.id=e.type_id
					LEFT JOIN varieties g ON g.id=e.variety_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
				 GROUP BY concat(concat(f.name,' '),g.name)
					)f ON f.tipe=a.name
					LEFT JOIN (
					SELECT concat(concat(d.name,' '),h.name) AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
						LEFT JOIN varieties h ON h.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY concat(concat(d.name,' '),h.name)
					)g ON g.tipe=a.name
					LEFT JOIN (
					SELECT concat(concat(d.name,' '),g.name) AS tipe, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
							LEFT JOIN varieties g ON g.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY concat(concat(d.name,' '),g.name)
					)h ON h.tipe=a.name order by name desc
        ");



		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;

		$sodailyglaze = 0.00;
		$moddailyglaze = 0.00;
		$sjdailyglaze = 0.00;
		$ossoglaze = 0.00;
		$osmodglaze = 0.00;
		$sjmtdglaze = 0.00;


		foreach ($query as $row) {
			$sodaily += $row->qtySO / 1.44;
			$moddaily += $row->qtyMOD / 1.44;
			$sjdaily += $row->qtySJ / 1.44;
			$osso += $row->sisaso / 1.44;
			$osmod += $row->sisamod / 1.44;
			$sjmtd += $row->sjm / 1.44;
		}

		$data[] = [
			'name' => 'TOTAL',
			'qtyso' => $sodaily,
			'qtymod' => $moddaily,
			'qtysj' => $sjdaily,
			'sisaso' => $osso,
			'sisamod' => $osmod,
			'sjm' => $sjmtd,
			'asp' => $aspglobal,
		];

		foreach ($query as $row) {
			if ($row->name == 'HT PLAIN') {
				$data[] = [
					'name'  => $row->name,
					'qtyso'  => $row->qtySO / 1.44,
					'qtymod'  => $row->qtyMOD / 1.44,
					'qtysj'  => $row->qtySJ / 1.44,
					'sisaso'  => $row->sisaso / 1.44,
					'sisamod'  => $row->sisamod / 1.44,
					'sjm'  => $row->sjm / 1.44,
					'asp'  => $row->asp,
				];
			}
		}

		foreach ($query as $row) {
			if ($row->name != 'HT PLAIN') {
				$sodailyglaze += $row->qtySO / 1.44;
				$moddailyglaze += $row->qtyMOD / 1.44;
				$sjdailyglaze += $row->qtySJ / 1.44;
				$ossoglaze += $row->sisaso / 1.44;
				$osmodglaze += $row->sisamod / 1.44;
				$sjmtdglaze += $row->sjm / 1.44;
			}
		}

		$data[] = [
			'name' => 'GLAZED PORCELAIN',
			'qtyso' => $sodailyglaze,
			'qtymod' => $moddailyglaze,
			'qtysj' => $sjdailyglaze,
			'sisaso' => $ossoglaze,
			'sisamod' => $osmodglaze,
			'sjm' => $sjmtdglaze,
			'asp' => $aspglaze,
		];

		foreach ($query as $row) {
			if ($row->name != 'HT PLAIN') {
				$data[] = [
					'name'  => $row->name,
					'qtyso'  => $row->qtySO / 1.44,
					'qtymod'  => $row->qtyMOD / 1.44,
					'qtysj'  => $row->qtySJ / 1.44,
					'sisaso'  => $row->sisaso / 1.44,
					'sisamod'  => $row->sisamod / 1.44,
					'sjm'  => $row->sjm / 1.44,
					'asp'  => $row->asp,
				];
			}
		}




		//oem 1b

		$query = DB::select("
     SELECT a.brand, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp
				
					FROM (SELECT NAME AS brand FROM brands WHERE deleted_at IS null)a LEFT JOIN (

					SELECT d.name AS brand, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
					
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name)b ON a.brand=b.brand
               LEFT JOIN (

               SELECT d.name AS brand, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id and b.deleted_at is null
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id and e.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)c ON c.brand=a.brand
					LEFT JOIN (
					SELECT d.name AS brand, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)d ON d.brand=a.brand
					LEFT JOIN (
					SELECT e.name AS brand,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
					LEFT JOIN brands e ON e.id=d.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY e.name
					)e ON e.brand=a.brand
					LEFT JOIN
					(
					SELECT f.name AS brand, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN items e ON e.id=b.item_id
					LEFT JOIN brands f ON f.id=e.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
				 GROUP BY f.name
					)f ON f.brand=a.brand
					LEFT JOIN (
					SELECT d.name AS brand, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
						
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY d.name
					)g ON g.brand=a.brand
					LEFT JOIN (
						SELECT d.name AS brand, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY d.name
					)h ON h.brand=a.brand
					 ORDER BY a.brand
					
        ");



		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;

		foreach ($query as $row) {
			$sodaily += $row->qtySO;
			$moddaily += $row->qtyMOD;
			$sjdaily += $row->qtySJ;
			$osso += $row->sisaso;
			$osmod += $row->sisamod;
			$sjmtd += $row->sjm;
		}

		$data2[] = [
			'name' => 'TOTAL',
			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspglobal,

		];

		foreach ($query as $row) {

			$data2[] = [
				'name'  => $row->brand,
				'qtyso'  => $row->qtySO / 1.44,
				'qtymod'  => $row->qtyMOD / 1.44,
				'qtysj'  => $row->qtySJ / 1.44,
				'sisaso'  => $row->sisaso / 1.44,
				'sisamod'  => $row->sisamod / 1.44,
				'sjm'  => $row->sjm / 1.44,
				'asp'  => $row->asp,

			];
		}


		//global 1c



		$query = DB::select("		  
                SELECT a.tipe,a.brand,
					 ifnull(e.sisaso,0) + ifnull(f.osmod,0) AS os,ifnull(d.qtysj,0) AS qtySJ , ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp
				
					FROM (SELECT b.name as tipe, a.NAME AS brand FROM brands a CROSS JOIN types b WHERE a.deleted_at IS null)a 
             
					LEFT JOIN (
					SELECT g.name as tipe,d.name AS brand, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
					LEFT JOIN types g ON g.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,g.name
					)d ON d.brand=a.brand AND d.tipe=a.tipe
					LEFT JOIN (
					SELECT f.name as tipe,e.name AS brand,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
					LEFT JOIN brands e ON e.id=d.brand_id
					LEFT JOIN types f ON f.id=d.type_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY e.name,f.name
					)e ON e.brand=a.brand AND e.tipe=a.tipe
					LEFT JOIN
					(
					SELECT g.`name` AS tipe,f.name AS brand, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN items e ON e.id=b.item_id
					LEFT JOIN brands f ON f.id=e.brand_id
					LEFT JOIN types g ON g.id=e.type_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
				 GROUP BY f.name, g.name
					)f ON f.brand=a.brand AND f.tipe=a.tipe
					LEFT JOIN (
					SELECT g.name as tipe,d.name AS brand, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
						LEFT JOIN types g ON g.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY d.name,g.name
					)g ON g.brand=a.brand AND g.tipe=a.tipe
					LEFT JOIN (
						SELECT g.name as tipe,d.name AS brand, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN brands d ON d.id=c.brand_id
					LEFT JOIN types g ON g.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY d.name,g.name
					)h ON h.brand=a.brand AND h.tipe=a.tipe
					 ORDER BY a.brand,a.tipe
						");

		$asp[] = ['name' => 'HT', 'nominal' => $aspht];
		$asp[] = ['name' => 'GP', 'nominal' => $aspglaze];


		foreach ($query as $row) {

			$data3[] = [
				'tipe'  => $row->tipe,
				'brand'  => $row->brand,
				'os'  => $row->os / 1.44,
				'qtysj'  => $row->qtySJ / 1.44,
				'sjm'  => $row->sjm / 1.44,
				'asp'  => $row->asp,


			];
		}

		//oem 2a
		$query = DB::select("
 
                   SELECT a.tipe,a.brand, IFNULL(b.php,0) AS PHP,IFNULL(d.endstock,0) AS stockm2,IFNULL(c.endstock,0) AS stockpalet
                   ,IFNULL(d.endstock/1.44,0) AS stockbox
					
					 FROM (SELECT b.name as tipe, a.NAME AS brand FROM brands a CROSS JOIN types b WHERE a.deleted_at IS null)a LEFT JOIN (
                SELECT e.name AS tipe, f.`name` AS brand, coalesce(SUM(b.qty*c.conversion),0) AS PHP
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					 LEFT JOIN types e ON e.id=d.type_id
					 LEFT JOIN brands f ON f.id=d.brand_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
                GROUP BY e.`name`,f.name)b ON a.tipe=b.tipe AND a.brand=b.brand
                LEFT JOIN (
                
 SELECT ts.name AS tipe, br.name AS brand, SUM(qty/conversion) AS endstock FROM items it LEFT JOIN  (
                SELECT d.id, coalesce(SUM(b.qty*c.conversion),0) AS Qty
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					 
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY d.id
                UNION ALL
                SELECT d.id, coalesce(SUM(b.qty),0)*-1 AS RepackOut
                FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_source_id and c.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_source_id
               
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY d.id
                UNION ALL
                SELECT d.id, coalesce(SUM(b.qty),0) AS RepackIn
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_target_id and c.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_target_id
               
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY d.id
                UNION ALL
                SELECT d.id, coalesce(SUM(b.qty),0) AS GR
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_id
               
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY d.id
                UNION ALL
                SELECT d.id, coalesce(SUM(b.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
               
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY d.id
                UNION ALL
               SELECT c.id, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					
					WHERE a.void_date is null AND a.deleted_at is NULL
               GROUP BY c.id)a ON a.id=it.id
               LEFT JOIN item_units iu ON iu.item_id=it.id and iu.deleted_at is null
               LEFT JOIN brands br ON br.id=it.brand_id
               LEFT JOIN types ts ON ts.id=it.type_id
               WHERE it.is_sales_item=1 AND it.pallet_id IN (5,6,7) AND iu.unit_id=60
               GROUP BY ts.name, br.name
					 )c ON c.tipe=a.tipe AND c.brand=a.brand
					 LEFT JOIN (
                SELECT tipe, brand, SUM(qty) AS endstock FROM (
                SELECT e.name AS tipe, f.name AS brand, coalesce(SUM(b.qty*c.conversion),0) AS Qty
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					 LEFT JOIN types e ON e.id=d.type_id
					 LEFT JOIN brands f ON f.id=d.brand_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name
                UNION ALL
                SELECT e.name AS tipe, f.name AS brand, coalesce(SUM(b.qty),0)*-1 AS RepackOut
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_source_id
                LEFT JOIN items d ON d.id=b.item_source_id
                LEFT JOIN types e ON e.id=d.type_id
                LEFT JOIN brands f ON f.id=d.brand_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name, f.name
                UNION ALL
                SELECT e.name AS tipe, f.name AS brand, coalesce(SUM(b.qty),0) AS RepackIn
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_target_id
                LEFT JOIN items d ON d.id=b.item_target_id
                LEFT JOIN types e ON e.id=d.type_id
                LEFT JOIN brands f ON f.id=d.brand_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name
                UNION ALL
                SELECT e.name AS tipe, f.`name` AS brand, coalesce(SUM(b.qty),0) AS GR
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name
                UNION ALL
                SELECT e.name AS tipe,f.`name` AS brand, coalesce(SUM(b.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name
                UNION ALL
               SELECT d.name AS tipe, g.name AS brand, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL
               GROUP BY d.name,g.name)a GROUP BY tipe,brand
					 )d ON d.tipe=a.tipe AND d.brand=a.brand order by tipe,brand
                    ");

		foreach ($query as $row) {

			$data4[] = [
				'tipe'  => $row->tipe,
				'brand'  => $row->brand,
				'php'  => $row->PHP,
				'm2'  => $row->stockm2,
				'palet'  => $row->stockpalet,
				'box'  => $row->stockbox,


			];
		}

		//oem 2b
		$query = DB::select("	
               SELECT brand ,tipe ,jenis,pattern,grade,CODE, SUM(qty) AS endstock FROM (
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty*c.conversion),0) AS Qty
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id and b.deleted_at is null
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					  INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
                	LEFT JOIN patterns i ON i.id=d.pattern_id
					LEFT JOIN grades j ON j.id=d.grade_id
					LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_source_id
                LEFT JOIN items d ON d.id=b.item_source_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
                	LEFT JOIN patterns i ON i.id=d.pattern_id
					LEFT JOIN grades j ON j.id=d.grade_id
						LEFT JOIN item_shadings k ON k.id=b.item_shading_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0) AS RepackIn
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_target_id
                LEFT JOIN items d ON d.id=b.item_target_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
                	LEFT JOIN patterns i ON i.id=d.pattern_id
					LEFT JOIN grades j ON j.id=d.grade_id
						LEFT JOIN item_shadings k ON k.id=b.item_shading_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0) AS GR
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
                	LEFT JOIN patterns i ON i.id=d.pattern_id
					LEFT JOIN grades j ON j.id=d.grade_id
						LEFT JOIN item_shadings k ON k.id=b.item_shading_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
                	LEFT JOIN patterns i ON i.id=d.pattern_id
					LEFT JOIN grades j ON j.id=d.grade_id
						LEFT JOIN item_shadings k ON k.id=b.item_shading_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
                UNION ALL
               SELECT d.`name` AS tipe,g.name AS brand,h.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN item_stocks l ON l.id=b.item_stock_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					LEFT JOIN varieties h ON h.id=c.variety_id
					LEFT JOIN patterns i ON i.id=c.pattern_id
					LEFT JOIN grades j ON j.id=c.grade_id
						LEFT JOIN item_shadings k ON k.id=l.item_shading_id
					WHERE a.void_date is null AND a.deleted_at is NULL
               GROUP BY d.name,g.`name`,h.name, i.name,j.name,k.code)a GROUP BY tipe,brand,jenis,pattern,grade,code");


		foreach ($query as $row) {

			$data5[] = [
				'brand'  => $row->brand,
				'tipe'  => $row->tipe,
				'jenis'  => $row->jenis,
				'pattern'  => $row->pattern,
				'grade'  => $row->grade,
				'code'  => $row->CODE,
				'endstock'  => $row->endstock,



			];
		}




		$obj = json_decode(json_encode($data));
		$obj2 = json_decode(json_encode($data2));
		$obj3 = json_decode(json_encode($data3));
		$obj4 = json_decode(json_encode($data4));
		$obj5 = json_decode(json_encode($data5));
		$asp = json_decode(json_encode($asp));


		Excel::store(new ExportStockOEM(), 'public/auto_email/stock.xlsx', 'local');
		Mail::to($recipient)->send(new SendMailMarketingOEM($obj, $obj2, $obj3, $obj4, $obj5, $asp));

		// }



	}
}
