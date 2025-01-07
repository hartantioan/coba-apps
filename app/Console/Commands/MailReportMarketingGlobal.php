<?php

namespace App\Console\Commands;


use App\Mail\SendMailMarketingGlobal;
use App\Models\GoodIssueDetail;
use App\Models\GoodReceiveDetail;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionRepackDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class MailReportMarketingGlobal extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'emailmarketingglobal:run';

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
		//$recipient = ['henrianto@superior.co.id', 'hendra@superior.co.id', 'andrew@superior.co.id', 'haidong@superiorporcelain.co.id', 'billylaw@superior.co.id', 'eunike@superior.co.id'];
		$recipient = ['edp@superior.co.id'];
		//  $akun = MarketingOrderInvoice::whereIn('status',[2])->distinct('account_id')->get('account_id');

		// foreach ($akun as $pangsit) {
		$data = [];
		$data2 = [];
		$data3 = [];
		$data4 = [];
		$data5 = [];
		$data6 = [];
		$data7 = [];

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


		//global 1a
		/*$query = DB::select("
              	SELECT a.name, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp
					FROM types a LEFT JOIN (

					SELECT d.name AS tipe, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name)b ON a.name=b.tipe
               LEFT JOIN (

               SELECT d.name AS tipe, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id and b.deleted_at is null
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id and e.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)c ON c.tipe=a.name
					LEFT JOIN (
					SELECT d.name AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)d ON d.tipe=a.name
					LEFT JOIN (
					SELECT e.name AS tipe,  sum((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
					LEFT JOIN types e ON e.id=d.type_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					GROUP BY e.name
					)e ON e.tipe=a.name
					LEFT JOIN
					(
					SELECT f.name AS tipe, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN items e ON e.id=b.item_id
					LEFT JOIN types f ON f.id=e.type_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
					GROUP BY f.name
					)f ON f.tipe=a.name
					LEFT JOIN (
					SELECT d.name AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)g ON g.tipe=a.name
					LEFT JOIN (
					SELECT d.name AS tipe, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)h ON h.tipe=a.name
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

			$data[] = [
				'name'  => $row->name,
				'qtyso'  => $row->qtySO,
				'qtymod'  => $row->qtyMOD,
				'qtysj'  => $row->qtySJ,
				'sisaso'  => $row->sisaso,
				'sisamod'  => $row->sisamod,
				'sjm'  => $row->sjm,
				'asp'  => $row->asp,
			];

			if ($row->name == 'GLAZED') {
				$aspgl = $row->asp;
			}

			if ($row->name == 'HT') {
				$aspht = $row->asp;
			}
		}
*/



		//global 1a

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

		$data2[] = [
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
				$data2[] = [
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

		$data2[] = [
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
				$data2[] = [
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


		//global 1b

		$aspht = 0;
		$aspglaze = 0;

		$query = DB::select("SELECT d.name AS tipe , sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY d.name");
		foreach ($query as $row) {
			if ($row->tipe == 'HT') {
				$aspht = $row->asp;
			}

			if ($row->tipe == 'GLAZED') {
				$aspglaze = $row->asp;
			}
		};



		$query = DB::select("
       SELECT distinct a.tipe,a.tipebrand, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp
				
					FROM (SELECT DISTINCT b.`name` AS tipe, d.`type` AS tipebrand FROM items a LEFT JOIN types b ON a.type_id=b.id
				
					LEFT JOIN brands d ON d.id=a.brand_id
					WHERE is_sales_item=1 AND a.type_id IS NOT NULL AND d.deleted_at IS null )a LEFT JOIN (
					SELECT d.name AS tipe ,f.type, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands f ON f.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,f.type)b ON a.tipe=b.tipe AND a.tipebrand=b.type
               LEFT JOIN (
               SELECT  d.name AS tipe,f.type, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id and e.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands f ON f.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,f.type
					)c ON a.tipe=c.tipe AND a.tipebrand=c.type
					LEFT JOIN (
					SELECT  d.name AS tipe ,g.type, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,g.type
					)d ON a.tipe=d.tipe AND a.tipebrand=d.type
					LEFT JOIN (
					SELECT e.name AS tipe ,f.type,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
					LEFT JOIN types e ON e.id=d.type_id
					LEFT JOIN brands f ON f.id=d.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY e.name,f.type
					)e ON a.tipe=e.tipe AND a.tipebrand=e.type
					LEFT JOIN
					(
					SELECT f.name AS tipe,g.type, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN items e ON e.id=b.item_id
					LEFT JOIN types f ON f.id=e.type_id
					LEFT JOIN brands g ON g.id=e.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
				 GROUP BY f.name,g.type
					)f ON a.tipe=f.tipe AND a.tipebrand=f.type
					LEFT JOIN (
					SELECT d.name AS tipe ,g.type, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY d.name,g.type
					)g ON a.tipe=g.tipe AND a.tipebrand=g.type
					LEFT JOIN (
					SELECT d.name AS tipe ,g.type, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY d.name,g.type
					)h ON a.tipe=h.tipe AND a.tipebrand=h.type ORDER BY tipe desc,tipebrand 
        ");


		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;

		foreach ($query as $row) {
			if ($row->tipe == 'HT') {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		}
		$data3[] = [
			'tipe' => 'HT',

			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspht,
		];

		foreach ($query as $row) {
			if ($row->tipe == 'HT' && $row->tipebrand == '1') {
				$data3[] = [
					'tipe' => 'HOUSE BRAND',

					'qtyso' => $row->qtySO / 1.44,
					'qtymod' => $row->qtyMOD / 1.44,
					'qtysj' => $row->qtySJ / 1.44,
					'sisaso' => $row->sisaso / 1.44,
					'sisamod' => $row->sisamod / 1.44,
					'sjm' => $row->sjm / 1.44,
					'asp' => $row->asp,
				];
			}

			if ($row->tipe == 'HT' && $row->tipebrand == '2') {
				$data3[] = [
					'tipe' => 'EXCLUSIVE BRAND',

					'qtyso' => $row->qtySO / 1.44,
					'qtymod' => $row->qtyMOD / 1.44,
					'qtysj' => $row->qtySJ / 1.44,
					'sisaso' => $row->sisaso / 1.44,
					'sisamod' => $row->sisamod / 1.44,
					'sjm' => $row->sjm / 1.44,
					'asp' => $row->asp,
				];
			}
		}

		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;


		foreach ($query as $row) {
			if ($row->tipe == 'GLAZED') {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		}
		$data3[] = [
			'tipe' => 'GLAZED',

			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspglaze,
		];

		foreach ($query as $row) {
			if ($row->tipe == 'GLAZED' && $row->tipebrand == '1') {
				$data3[] = [
					'tipe' => 'HOUSE BRAND',

					'qtyso' => $row->qtySO / 1.44,
					'qtymod' => $row->qtyMOD / 1.44,
					'qtysj' => $row->qtySJ / 1.44,
					'sisaso' => $row->sisaso / 1.44,
					'sisamod' => $row->sisamod / 1.44,
					'sjm' => $row->sjm / 1.44,
					'asp' => $row->asp,
				];
			}
			if ($row->tipe == 'GLAZED' && $row->tipebrand == '2') {
				$data3[] = [
					'tipe' => 'EXCLUSIVE BRAND',

					'qtyso' => $row->qtySO / 1.44,
					'qtymod' => $row->qtyMOD / 1.44,
					'qtysj' => $row->qtySJ / 1.44,
					'sisaso' => $row->sisaso / 1.44,
					'sisamod' => $row->sisamod / 1.44,
					'sjm' => $row->sjm / 1.44,
					'asp' => $row->asp,
				];
			}
		}


		//global 1c

		$asphome = 0;
		$aspexclusive = 0;

		$query = DB::select("SELECT g.type AS tipe , sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY g.type");
		foreach ($query as $row) {
			if ($row->tipe == '1') {
				$asphome = $row->asp;
			}

			if ($row->tipe == '2') {
				$aspexclusive = $row->asp;
			}
		};


		$query = DB::select("SELECT distinct a.brand,a.tipebrand, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp
				
					FROM (SELECT DISTINCT d.`name` AS brand, d.`type` AS tipebrand FROM items a
					LEFT JOIN brands d ON d.id=a.brand_id
					WHERE is_sales_item=1 AND a.type_id IS NOT NULL AND d.deleted_at IS null )a LEFT JOIN (
					SELECT f.name AS brand,f.type, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					
					LEFT JOIN brands f ON f.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY f.name,f.type)b ON a.brand=b.brand AND a.tipebrand=b.type
               LEFT JOIN (
               SELECT  f.name AS brand,f.type, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id and e.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
				
					LEFT JOIN brands f ON f.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY f.name,f.type
					)c ON a.brand=c.brand AND a.tipebrand=c.type
					LEFT JOIN (
					SELECT  g.name AS brand ,g.type, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
				
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY g.name,g.type
					)d ON a.brand=d.brand AND a.tipebrand=d.type
					LEFT JOIN (
					SELECT f.name AS brand ,f.type,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
				
					LEFT JOIN brands f ON f.id=d.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY f.name,f.type
					)e ON a.brand=e.brand AND a.tipebrand=e.type
					LEFT JOIN
					(
					SELECT g.name AS brand,g.type, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN items e ON e.id=b.item_id
					
					LEFT JOIN brands g ON g.id=e.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
				 GROUP BY g.name,g.type
					)f ON a.brand=f.brand AND a.tipebrand=f.type
					LEFT JOIN (
					SELECT g.name AS brand ,g.type, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY g.name,g.type
					)g ON a.brand=g.brand AND a.tipebrand=g.type
					LEFT JOIN (
					SELECT g.name AS brand ,g.type, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY g.name,g.type
					)h ON a.brand=h.brand AND a.tipebrand=h.type ORDER BY tipebrand,brand ");

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
		};

		$data[] = [
			'tipe' => 'TOTAL',
			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspglobal,
		];

		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;

		foreach ($query as $row) {
			if ($row->tipebrand == '2') {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		};

		$data[] = [
			'tipe' => 'EXCLUSIVE BRAND',
			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspexclusive,
		];

		foreach ($query as $row) {
			if ($row->tipebrand == '2') {
				$data[] = [
					'tipe'  => $row->brand,
					'qtyso'  => $row->qtySO / 1.44,
					'qtymod'  => $row->qtyMOD / 1.44,
					'qtysj'  => $row->qtySJ / 1.44,
					'sisaso'  => $row->sisaso / 1.44,
					'sisamod'  => $row->sisamod / 1.44,
					'sjm'  => $row->sjm / 1.44,
					'asp'  => $row->asp,
				];
			}
		};

		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;

		foreach ($query as $row) {
			if ($row->tipebrand == '1') {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		};

		$data[] = [
			'tipe' => 'HOUSE BRAND',
			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $asphome,
		];

		foreach ($query as $row) {
			if ($row->tipebrand == '1') {
				$data[] = [
					'tipe'  => $row->brand,
					'qtyso'  => $row->qtySO / 1.44,
					'qtymod'  => $row->qtyMOD / 1.44,
					'qtysj'  => $row->qtySJ / 1.44,
					'sisaso'  => $row->sisaso / 1.44,
					'sisamod'  => $row->sisamod / 1.44,
					'sjm'  => $row->sjm / 1.44,
					'asp'  => $row->asp,
				];
			}
		};

		//global 1d

		$aspjawa = 0;
		$aspluarjawa = 0;

		$query = DB::select("SELECT r.category_region AS area, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN marketing_orders g ON g.id=f.marketing_order_id AND g.deleted_at IS NULL AND g.void_date IS null
					LEFT JOIN regions r ON r.id=g.province_id
					left join items c on c.id=e.item_id
					left join brands h on h.id=c.brand_id
					WHERE h.type=1 and a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY r.category_region");
		foreach ($query as $row) {
			if ($row->area==1)
			{
				$aspjawa=$row->asp;
			}
			if ($row->area==2)
			{
				$aspluarjawa=$row->asp;
			}
		}



		$query = DB::select("SELECT DISTINCT a.sale_area,a.category_region, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp
				
					FROM (SELECT sale_area,category_region  FROM regions WHERE length(CODE)=2 and deleted_at IS null)a LEFT JOIN (
					SELECT r.sale_area AS area, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
						LEFT JOIN user_datas ud ON ud.id=a.account_id
				   LEFT JOIN regions r ON r.id=ud.province_id
				   LEFT JOIN items c ON c.id=b.item_id
					
					LEFT JOIN brands f ON f.id=c.brand_id
					WHERE f.type=1 and a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY r.sale_area)b ON a.sale_area=b.area
               LEFT JOIN (
               SELECT r.sale_area AS area, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id and e.deleted_at is null
					LEFT JOIN marketing_orders f ON f.id=e.marketing_order_id AND f.deleted_at IS NULL AND f.void_date IS null
					LEFT JOIN user_datas ud ON ud.id=f.account_id
					LEFT JOIN regions r ON r.id=ud.province_id
					LEFT JOIN items c ON c.id=b.item_id
				
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE g.type=1 and a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY r.sale_area
					)c ON c.area = a.sale_area
					LEFT JOIN (
					SELECT  r.sale_area AS area, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN marketing_orders g ON g.id=f.marketing_order_id AND g.deleted_at IS NULL AND g.void_date IS null
					LEFT JOIN user_datas ud ON ud.id=g.account_id
					LEFT JOIN regions r ON r.id=ud.province_id
					LEFT JOIN items c ON c.id=e.item_id
				
					LEFT JOIN brands h ON h.id=c.brand_id
					WHERE h.type=1 and a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY r.sale_area
					)d ON d.area=a.sale_area
					LEFT JOIN (
					SELECT r.sale_area AS area,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
						LEFT JOIN user_datas ud ON ud.id=a.account_id
					LEFT JOIN regions r ON r.id=ud.province_id
					LEFT JOIN items d ON d.id=b.item_id
				
					LEFT JOIN brands f ON f.id=d.brand_id
					WHERE f.type=1 and a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY r.sale_area
					)e ON e.area=a.sale_area
					LEFT JOIN
					(
					SELECT r.sale_area AS area, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN marketing_orders g ON g.id=c.marketing_order_id AND g.deleted_at IS NULL AND g.void_date IS null
						LEFT JOIN user_datas ud ON ud.id=g.account_id
					LEFT JOIN regions r ON r.id=ud.province_id
					LEFT JOIN items e ON e.id=b.item_id
					
					LEFT JOIN brands h ON g.id=e.brand_id
					WHERE h.type=1 and a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
				 GROUP BY r.sale_area
					)f ON f.area=a.sale_area
					LEFT JOIN (
					SELECT r.sale_area AS area, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN marketing_orders g ON g.id=f.marketing_order_id AND g.deleted_at IS NULL AND g.void_date IS null
						LEFT JOIN user_datas ud ON ud.id=g.account_id
					LEFT JOIN regions r ON r.id=ud.province_id
					LEFT JOIN items c ON c.id=e.item_id
					
					LEFT JOIN brands h ON g.id=c.brand_id
					WHERE h.type=1 and a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY r.sale_area
					)g ON g.area=a.sale_area
					LEFT JOIN (
					SELECT r.sale_area AS area, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN marketing_orders g ON g.id=f.marketing_order_id AND g.deleted_at IS NULL AND g.void_date IS null
					LEFT JOIN user_datas ud ON ud.id=g.account_id
					LEFT JOIN regions r ON r.id=ud.province_id
					LEFT JOIN items c ON c.id=e.item_id
					
					LEFT JOIN brands h ON g.id=c.brand_id
					WHERE h.type=1 and a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY r.sale_area
					)h ON h.area=a.sale_area ORDER BY category_region,sale_area");

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
		};

		$data7[] = [
			'tipe' => 'TOTAL',
			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspglobal,
		];

		$kota = '';

		foreach ($query as $row) {
			if ($row->category_region == 1) {
				$kota = match ($row->sale_area) {
					'1' => 'SUMATERA',
					'2' => 'DKI JAKARTA JABAR',
					'3' => 'BALI NUSRA',
					'4' => 'JAWA TENGAH',
					'5' => 'JAWA TIMUR',
					'6' => 'KALIMANTAN',
					'7' => 'SULAWESI',
					'8' => 'MALUKU PAPUA',
				};

				$data7[] = [
					'tipe' => $kota,
					'qtyso' => $row->qtySO/1.44,
					'qtymod' => $row->qtyMOD/1.44,
					'qtysj' => $row->qtySJ/1.44,
					'sisaso' => $row->sisaso/1.44,
					'sisamod' => $row->sisamod/1.44,
					'sjm' => $row->sjm/1.44,
					'asp' => $row->asp,
				];
			}
		}
		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;
		foreach ($query as $row) {
			if ($row->category_region == 1) {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		}

		$data7[] = [
			'tipe' => 'TOTAL JAWA',
			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspjawa,
		];

		$kota = '';

		foreach ($query as $row) {
			if ($row->category_region == 2) {
				$kota = match ($row->sale_area) {
					'1' => 'SUMATERA',
					'2' => 'DKI JAKARTA JABAR',
					'3' => 'BALI NUSRA',
					'4' => 'JAWA TENGAH',
					'5' => 'JAWA TIMUR',
					'6' => 'KALIMANTAN',
					'7' => 'SULAWESI',
					'8' => 'MALUKU PAPUA',
				};

				$data7[] = [
					'tipe' => $kota,
					'qtyso' => $row->qtySO/1.44,
					'qtymod' => $row->qtyMOD/1.44,
					'qtysj' => $row->qtySJ/1.44,
					'sisaso' => $row->sisaso/1.44,
					'sisamod' => $row->sisamod/1.44,
					'sjm' => $row->sjm/1.44,
					'asp' => $row->asp,
				];
			}
		}
		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;
		foreach ($query as $row) {
			if ($row->category_region == 2) {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		}

		$data7[] = [
			'tipe' => 'TOTAL LUAR JAWA',
			'qtyso' => $sodaily / 1.44,
			'qtymod' => $moddaily / 1.44,
			'qtysj' => $sjdaily / 1.44,
			'sisaso' => $osso / 1.44,
			'sisamod' => $osmod / 1.44,
			'sjm' => $sjmtd / 1.44,
			'asp' => $aspluarjawa,
		];

		//global 1c
		/*
		$query = DB::select("

					SELECT a.name as tipe,z.name as grup, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm, ifnull(round(h.asp,2),0) AS asp

					FROM types a CROSS JOIN (select name from `groups` WHERE type='2') z left JOIN (
					SELECT d.name AS tipe, f.name AS grup, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN users e ON e.id=a.account_id
					LEFT JOIN `groups` f ON f.id=e.group_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,f.name )b ON a.name=b.tipe AND b.grup=z.name
               LEFT JOIN (

               SELECT d.name AS tipe, g.name AS grup, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id and b.deleted_at is null
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id and e.deleted_at is null
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN users f ON f.id=a.customer_id
					LEFT JOIN `groups` g ON g.id=f.group_id

					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,g.name
					)c ON c.tipe=a.name AND c.grup=z.name
					LEFT JOIN (
					SELECT d.name AS tipe,h.name AS grup, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
						LEFT JOIN marketing_orders gg ON gg.id=f.marketing_order_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN users g ON g.id=gg.account_id
					LEFT JOIN `groups` h ON h.id=g.group_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,h.name
					)d ON d.tipe=a.name
					LEFT JOIN (
					SELECT e.name AS tipe, g.name AS grup, sum((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id and b.deleted_at is null
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
					LEFT JOIN types e ON e.id=d.type_id
					LEFT JOIN users f ON f.id=a.account_id
					LEFT JOIN `groups` g ON g.id=f.group_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY e.name,g.name
					)e ON e.tipe=a.name AND e.grup=z.name
					LEFT JOIN
					(
					SELECT f.name AS tipe, h.name AS grup, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id and c.deleted_at is null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id and d.deleted_at is null
					LEFT JOIN items e ON e.id=b.item_id
					LEFT JOIN types f ON f.id=e.type_id
					LEFT JOIN users g ON g.id=a.customer_id
					LEFT JOIN `groups` h ON h.id=g.group_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
				 GROUP BY f.name,h.name
					)f ON f.tipe=a.name AND f.grup=z.name
					LEFT JOIN (
					SELECT d.name AS tipe,h.name AS grup, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
						LEFT JOIN marketing_orders gg ON gg.id=f.marketing_order_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
						LEFT JOIN users g ON g.id=gg.account_id
					LEFT JOIN `groups` h ON h.id=g.group_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY d.name,h.name
					)g ON g.tipe=a.name AND g.grup=z.name
					LEFT JOIN (
					SELECT d.name AS tipe, h.name AS grup, sum(case when f.is_include_tax='1' then (b.qty*f.qty_conversion*(f.price/((100+f.percent_tax)/100)))
					ELSE (b.qty*f.qty_conversion*f.price) END)/SUM(b.qty*f.qty_conversion) AS 'asp'
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN marketing_orders gg ON gg.id=f.marketing_order_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN users g ON g.id=gg.account_id
					LEFT JOIN `groups` h ON h.id=g.group_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
              	 GROUP BY d.name,h.name
					)h ON h.tipe=a.name AND h.grup=z.name;
        ");



		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;

		foreach ($query as $row) {
			if ($row->tipe == 'HT') {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		}

		$data3[] = [
			'tipe' => 'HT',

			'qtyso' => $sodaily,
			'qtymod' => $moddaily,
			'qtysj' => $sjdaily,
			'sisaso' => $osso,
			'sisamod' => $osmod,
			'sjm' => $sjmtd,
			'asp' => $aspht,
		];

		foreach ($query as $row) {
			if ($row->tipe == 'HT') {
				$data3[] = [
					'tipe'  => $row->grup,
					'qtyso'  => $row->qtySO,
					'qtymod'  => $row->qtyMOD,
					'qtysj'  => $row->qtySJ,
					'sisaso'  => $row->sisaso,
					'sisamod'  => $row->sisamod,
					'sjm'  => $row->sjm,
					'asp'  => $row->asp,
				];
			}
		}

		$sodaily = 0.00;
		$moddaily = 0.00;
		$sjdaily = 0.00;
		$osso = 0.00;
		$osmod = 0.00;
		$sjmtd = 0.00;

		foreach ($query as $row) {
			if ($row->tipe == 'GLAZED') {
				$sodaily += $row->qtySO;
				$moddaily += $row->qtyMOD;
				$sjdaily += $row->qtySJ;
				$osso += $row->sisaso;
				$osmod += $row->sisamod;
				$sjmtd += $row->sjm;
			}
		}

		$data3[] = [
			'tipe' => 'GLAZED',
			'qtyso' => $sodaily,
			'qtymod' => $moddaily,
			'qtysj' => $sjdaily,
			'sisaso' => $osso,
			'sisamod' => $osmod,
			'sjm' => $sjmtd,
			'asp' => $aspgl,
		];

		foreach ($query as $row) {
			if ($row->tipe == 'GLAZED') {
				$data3[] = [
					'tipe'  => $row->grup,
					'qtyso'  => $row->qtySO,
					'qtymod'  => $row->qtyMOD,
					'qtysj'  => $row->qtySJ,
					'sisaso'  => $row->sisaso,
					'sisamod'  => $row->sisamod,
					'sjm'  => $row->sjm,
					'asp'  => $row->asp,
				];
			}
		}*/



		//global 2a
		$query = DB::select("

                   SELECT a.name, IFNULL(b.php,0) AS PHP, IFNULL(c.qtysj,0) AS sales,IFNULL(d.endstock,0) AS stock, coalesce(IFNULL(d.endstock,0)/ IFNULL(c.qtysj,0),0) AS life
					  FROM (SELECT distinct concat(concat(b.name,' '),c.name) AS name FROM items a LEFT JOIN types b ON a.type_id=b.id
					LEFT JOIN varieties c ON c.id=a.variety_id
					WHERE is_sales_item=1 AND a.type_id IS NOT NULL)a LEFT JOIN (
                SELECT concat(concat(e.name,' '),f.name) AS tipe, coalesce(SUM(b.qty*c.conversion),0) AS PHP
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					 LEFT JOIN types e ON e.id=d.type_id
					 LEFT JOIN varieties f ON f.id=d.variety_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
                GROUP BY concat(concat(e.name,' '),f.name))b ON a.`name`=b.tipe
                LEFT JOIN
                (
                  SELECT concat(concat(d.name,' '),g.name) AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties g ON g.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY concat(concat(d.name,' '),g.name)
					 )c ON c.tipe=a.name
					 LEFT JOIN (
                SELECT tipe, SUM(qty) AS endstock FROM (
                SELECT concat(concat(e.name,' '),f.name) AS tipe, coalesce(SUM(b.qty*c.conversion),0) AS Qty
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					 LEFT JOIN types e ON e.id=d.type_id
					 LEFT JOIN varieties f ON f.id=d.variety_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY concat(concat(e.name,' '),f.name)
                UNION ALL
                SELECT concat(concat(e.name,' '),f.name) AS tipe, coalesce(SUM(b.qty),0)*-1 AS RepackOut
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_source_id
                LEFT JOIN items d ON d.id=b.item_source_id
                LEFT JOIN types e ON e.id=d.type_id
                LEFT JOIN varieties f ON f.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY concat(concat(e.name,' '),f.name)
                UNION ALL
                SELECT concat(concat(e.name,' '),f.name) AS tipe, coalesce(SUM(b.qty),0) AS RepackIn
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_target_id
                LEFT JOIN items d ON d.id=b.item_target_id
                LEFT JOIN types e ON e.id=d.type_id
                LEFT JOIN varieties f ON f.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY concat(concat(e.name,' '),f.name)
                UNION ALL
                SELECT concat(concat(e.name,' '),f.name) AS tipe, coalesce(SUM(b.qty),0) AS GR
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN varieties f ON f.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY concat(concat(e.name,' '),f.name)
                UNION ALL
                SELECT concat(concat(e.name,' '),f.name) AS tipe, coalesce(SUM(b.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN varieties f ON f.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY concat(concat(e.name,' '),f.name)
                UNION ALL
               SELECT concat(concat(d.name,' '),g.name) AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties g ON g.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL
               GROUP BY concat(concat(d.name,' '),g.name))a GROUP BY tipe
					 )d ON d.tipe=a.name order by name desc
                    ");



		$php = 0.00;
		$sales = 0.00;
		$stock = 0.00;
		$life = 0.00;


		foreach ($query as $row) {

			$php += $row->PHP;
			$sales += $row->sales;
			$stock += $row->stock;
			$life += $row->life;
		}

		$data4[] = [
			'name' => 'TOTAL',
			'php' => $php / 1.44,
			'sales' => $sales / 1.44,
			'stock' => $stock / 1.44,
			'life' => $life,

		];

		foreach ($query as $row) {
			$data4[] = [
				'name' => $row->name,
				'php' => $row->PHP / 1.44,
				'sales' => $row->sales / 1.44,
				'stock' => $row->stock / 1.44,
				'life' => $row->life,

			];
		}

		//global 2b
		/*
		$query = DB::select("

            SELECT tipe,brand,grade, SUM(qty) AS endstock FROM (
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS grade, coalesce(SUM(b.qty*c.conversion),0) AS Qty
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					  INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN grades g ON g.id=d.grade_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS grade, coalesce(SUM(b.qty),0)*-1 AS RepackOut
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_source_id
                LEFT JOIN items d ON d.id=b.item_source_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN grades g ON g.id=d.grade_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS grade, coalesce(SUM(b.qty),0) AS RepackIn
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_target_id
                LEFT JOIN items d ON d.id=b.item_target_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN grades g ON g.id=d.grade_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS grade, coalesce(SUM(b.qty),0) AS GR
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN grades g ON g.id=d.grade_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS grade, coalesce(SUM(b.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN grades g ON g.id=d.grade_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name,g.name
                UNION ALL
               SELECT d.`name` AS tipe,g.name AS brand,h.name AS grade, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and b.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					LEFT JOIN grades h ON h.id=c.grade_id
					WHERE a.void_date is null AND a.deleted_at is NULL
               GROUP BY d.name,g.`name`,h.name)a GROUP BY tipe,brand,grade
            ");

		foreach ($query as $row) {
			$data5[] = [
				'tipe' => $row->tipe,
				'brand' => $row->brand,
				'grade' => $row->grade,
				'endstock' => $row->endstock,


			];
		}
*/

		$query = DB::select("SELECT a.tipe,a.brand, IFNULL(b.php,0) AS PHP, IFNULL(c.qtysj,0) AS sales,IFNULL(d.endstock,0) AS stock, coalesce(IFNULL(d.endstock,0)/ IFNULL(c.qtysj,0),0) AS life
					  FROM (SELECT DISTINCT b.name AS tipe, c.name AS brand  FROM items a LEFT JOIN types b ON a.type_id=b.id
					LEFT JOIN brands c ON c.id=a.brand_id
					WHERE is_sales_item=1 AND a.type_id IS NOT NULL)a LEFT JOIN (
                SELECT e.name as tipe, f.name AS brand, coalesce(SUM(b.qty*c.conversion),0) AS PHP
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					 LEFT JOIN types e ON e.id=d.type_id
					 LEFT JOIN brands f ON f.id=d.brand_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
                GROUP BY e.name,f.name)b ON a.`tipe`=b.tipe AND b.brand=a.brand
                LEFT JOIN
                (
                  SELECT d.name AS tipe, g.`name` AS brand, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name,g.name
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
                SELECT e.name AS tipe,f.name AS brand,  coalesce(SUM(b.qty),0)*-1 AS RepackOut
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                LEFT JOIN item_units c ON c.id=item_unit_source_id
                LEFT JOIN items d ON d.id=b.item_source_id
                LEFT JOIN types e ON e.id=d.type_id
                LEFT JOIN brands f ON f.id=d.brand_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name
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
                SELECT e.name AS tipe, f.name AS brand, coalesce(SUM(b.qty),0) AS GR
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                LEFT JOIN items d ON d.id=b.item_id
                INNER JOIN types e ON e.id=d.type_id
                LEFT JOIN brands f ON f.id=d.brand_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name
                UNION ALL
                SELECT e.name AS tipe, f.name AS brand, coalesce(SUM(b.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
                INNER JOIN types e ON e.id=d.type_id
                LEFT JOIN brands f ON f.id=d.brand_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name
                UNION ALL
               SELECT d.name AS tipe, g.`name` AS brand, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL
               GROUP BY d.name,g.name)a GROUP BY tipe,brand
					 )d ON d.tipe=a.tipe AND d.brand=a.brand order by brand ");

		$php = 0;
		$sales = 0;
		$endstock = 0;
		$life = 0;

		foreach ($query as $row) {
			if (($row->tipe == 'HT' && $row->brand == 'AURORA') || ($row->tipe == 'HT' && $row->brand == 'ARA') || ($row->tipe == 'HT' && $row->brand == 'REXTON') || ($row->tipe == 'HT' && $row->brand == 'CARLO') || ($row->tipe == 'HT' && $row->brand == 'CORE') || ($row->tipe == 'HT' && $row->brand == 'EOS') || ($row->tipe == 'HT' && $row->brand == 'MAHESA') || ($row->tipe == 'HT' && $row->brand == 'REMO') || ($row->tipe == 'HT' && $row->brand == 'VALDA') || ($row->tipe == 'HT' && $row->brand == 'VALERIO') || ($row->tipe == 'HT' && $row->brand == 'NO BRAND')) {
				$php  += $row->PHP;
				$sales  += $row->sales;
				$endstock  += $row->stock;
			}
		}

		if ($sales != 0) {
			$life = $endstock / $sales;
		}

		$data5[] = [
			'tipe' => 'TOTAL',
			'php' => $php / 1.44,
			'sales' => $sales / 1.44,
			'stock' => $endstock / 1.44,
			'life' => $life,
		];

		$php = 0;
		$sales = 0;
		$endstock = 0;
		$life = 0;

		foreach ($query as $row) {
			if (($row->tipe == 'HT' && $row->brand == 'AURORA') || ($row->tipe == 'HT' && $row->brand == 'ARA') || ($row->tipe == 'HT' && $row->brand == 'REXTON')) {
				$php  += $row->PHP;
				$sales  += $row->sales;
				$endstock  += $row->stock;
			}
		}
		if ($sales != 0) {
			$life = $endstock / $sales;
		}
		foreach ($query as $row) {
			if (($row->tipe == 'HT' && $row->brand == 'AURORA') || ($row->tipe == 'HT' && $row->brand == 'ARA') || ($row->tipe == 'HT' && $row->brand == 'REXTON')) {
				$data5[] = [
					'tipe' => $row->brand,
					'php' => $row->PHP / 1.44,
					'sales' => $row->sales / 1.44,
					'stock' => $row->stock / 1.44,
					'life' => $row->life,
				];
			}
		}

		$data5[] = [
			'tipe' => 'SUBTOTAL',
			'php' => $php / 1.44,
			'sales' => $sales / 1.44,
			'stock' => $endstock / 1.44,
			'life' => $life,
		];

		$php = 0;
		$sales = 0;
		$endstock = 0;
		$life = 0;

		foreach ($query as $row) {
			if (($row->tipe == 'HT' && $row->brand == 'CARLO') || ($row->tipe == 'HT' && $row->brand == 'CORE') || ($row->tipe == 'HT' && $row->brand == 'EOS') || ($row->tipe == 'HT' && $row->brand == 'MAHESA') || ($row->tipe == 'HT' && $row->brand == 'REMO') || ($row->tipe == 'HT' && $row->brand == 'VALDA') || ($row->tipe == 'HT' && $row->brand == 'VALERIO') || ($row->tipe == 'HT' && $row->brand == 'NO BRAND')) {
				$php  += $row->PHP;
				$sales  += $row->sales;
				$endstock  += $row->stock;
			}
		}
		if ($sales != 0) {
			$life = $endstock / $sales;
		}
		foreach ($query as $row) {
			if (($row->tipe == 'HT' && $row->brand == 'CARLO') || ($row->tipe == 'HT' && $row->brand == 'CORE') || ($row->tipe == 'HT' && $row->brand == 'EOS') || ($row->tipe == 'HT' && $row->brand == 'MAHESA') || ($row->tipe == 'HT' && $row->brand == 'REMO') || ($row->tipe == 'HT' && $row->brand == 'VALDA') || ($row->tipe == 'HT' && $row->brand == 'VALERIO') || ($row->tipe == 'HT' && $row->brand == 'NO BRAND')) {
				$data5[] = [
					'tipe' => $row->brand,
					'php' => $row->PHP / 1.44,
					'sales' => $row->sales / 1.44,
					'stock' => $row->stock / 1.44,
					'life' => $row->life,
				];
			}
		}

		$data5[] = [
			'tipe' => 'SUBTOTAL',
			'php' => $php / 1.44,
			'sales' => $sales / 1.44,
			'stock' => $endstock / 1.44,
			'life' => $life,
		];




		$php = 0;
		$sales = 0;
		$endstock = 0;
		$life = 0;

		foreach ($query as $row) {
			if (($row->tipe == 'GLAZED' && $row->brand == 'AURORA') || ($row->tipe == 'GLAZED' && $row->brand == 'ARA') || ($row->tipe == 'GLAZED' && $row->brand == 'REXTON') || ($row->tipe == 'GLAZED' && $row->brand == 'CARLO') || ($row->tipe == 'GLAZED' && $row->brand == 'CORE') || ($row->tipe == 'GLAZED' && $row->brand == 'EOS') || ($row->tipe == 'GLAZED' && $row->brand == 'MAHESA') || ($row->tipe == 'GLAZED' && $row->brand == 'REMO') || ($row->tipe == 'GLAZED' && $row->brand == 'VALDA') || ($row->tipe == 'GLAZED' && $row->brand == 'VALERIO') || ($row->tipe == 'GLAZED' && $row->brand == 'NO BRAND')) {
				$php  += $row->PHP;
				$sales  += $row->sales;
				$endstock  += $row->stock;
			}
		}

		if ($sales != 0) {
			$life = $endstock / $sales;
		}

		$data6[] = [
			'tipe' => 'TOTAL',
			'php' => $php / 1.44,
			'sales' => $sales / 1.44,
			'stock' => $endstock / 1.44,
			'life' => $life,
		];

		$php = 0;
		$sales = 0;
		$endstock = 0;
		$life = 0;

		foreach ($query as $row) {
			if (($row->tipe == 'GLAZED' && $row->brand == 'AURORA') || ($row->tipe == 'GLAZED' && $row->brand == 'ARA') || ($row->tipe == 'GLAZED' && $row->brand == 'REXTON')) {
				$php  += $row->PHP;
				$sales  += $row->sales;
				$endstock  += $row->stock;
			}
		}
		if ($sales != 0) {
			$life = $endstock / $sales;
		}
		foreach ($query as $row) {
			if (($row->tipe == 'GLAZED' && $row->brand == 'AURORA') || ($row->tipe == 'GLAZED' && $row->brand == 'ARA') || ($row->tipe == 'GLAZED' && $row->brand == 'REXTON')) {
				$data6[] = [
					'tipe' => $row->brand,
					'php' => $row->PHP / 1.44,
					'sales' => $row->sales / 1.44,
					'stock' => $row->stock / 1.44,
					'life' => $row->life,
				];
			}
		}

		$data6[] = [
			'tipe' => 'SUBTOTAL',
			'php' => $php / 1.44,
			'sales' => $sales / 1.44,
			'stock' => $endstock / 1.44,
			'life' => $life,
		];

		$php = 0;
		$sales = 0;
		$endstock = 0;
		$life = 0;

		foreach ($query as $row) {
			if (($row->tipe == 'GLAZED' && $row->brand == 'CARLO') || ($row->tipe == 'GLAZED' && $row->brand == 'CORE') || ($row->tipe == 'GLAZED' && $row->brand == 'EOS') || ($row->tipe == 'GLAZED' && $row->brand == 'MAHESA') || ($row->tipe == 'GLAZED' && $row->brand == 'REMO') || ($row->tipe == 'GLAZED' && $row->brand == 'VALDA') || ($row->tipe == 'GLAZED' && $row->brand == 'VALERIO') || ($row->tipe == 'GLAZED' && $row->brand == 'NO BRAND')) {
				$php  += $row->PHP;
				$sales  += $row->sales;
				$endstock  += $row->stock;
			}
		}
		if ($sales != 0) {
			$life = $endstock / $sales;
		}
		foreach ($query as $row) {
			if (($row->tipe == 'GLAZED' && $row->brand == 'CARLO') || ($row->tipe == 'GLAZED' && $row->brand == 'CORE') || ($row->tipe == 'GLAZED' && $row->brand == 'EOS') || ($row->tipe == 'GLAZED' && $row->brand == 'MAHESA') || ($row->tipe == 'GLAZED' && $row->brand == 'REMO') || ($row->tipe == 'GLAZED' && $row->brand == 'VALDA') || ($row->tipe == 'GLAZED' && $row->brand == 'VALERIO') || ($row->tipe == 'GLAZED' && $row->brand == 'NO BRAND')) {
				$data6[] = [
					'tipe' => $row->brand,
					'php' => $row->PHP / 1.44,
					'sales' => $row->sales / 1.44,
					'stock' => $row->stock / 1.44,
					'life' => $row->life,
				];
			}
		}

		$data6[] = [
			'tipe' => 'SUBTOTAL',
			'php' => $php / 1.44,
			'sales' => $sales / 1.44,
			'stock' => $endstock / 1.44,
			'life' => $life,
		];



		$obj = json_decode(json_encode($data));
		$obj2 = json_decode(json_encode($data2));
		$obj3 = json_decode(json_encode($data3));
		$obj4 = json_decode(json_encode($data4));
		$obj5 = json_decode(json_encode($data5));
		$obj6 = json_decode(json_encode($data6));
		$obj7 = json_decode(json_encode($data7));


		Mail::to($recipient)->send(new SendMailMarketingGlobal($obj, $obj2, $obj3, $obj4, $obj5, $obj6, $obj7));

		// }



	}
}
