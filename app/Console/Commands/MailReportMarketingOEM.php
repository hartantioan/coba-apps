<?php

namespace App\Console\Commands;


use App\Mail\SendMailMarketingOEM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

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
		$recipient = ['edp@superior.co.id'];
		//  $akun = MarketingOrderInvoice::whereIn('status',[2])->distinct('account_id')->get('account_id');

		// foreach ($akun as $pangsit) {
		$data = [];
		$data2 = [];
		$data3 = [];
		$data4 = [];
		$data5 = [];



		//global 1a
		$query = DB::select("
              	SELECT a.name, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm
					FROM types a LEFT JOIN (

					SELECT d.name AS tipe, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name)b ON a.name=b.tipe
               LEFT JOIN (

               SELECT d.name AS tipe, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)c ON c.tipe=a.name
					LEFT JOIN (
					SELECT d.name AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)d ON d.tipe=a.name
					LEFT JOIN (
					SELECT e.name AS tipe,  sum((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id
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
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id
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
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY d.name
					)g ON g.tipe=a.name
					
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

			];
		}




		//global 1b

		$query = DB::select("
       	SELECT a.name, ifnull(b.qtyso,0) AS qtySO,ifnull(c.qtymod,0) AS qtyMOD,ifnull(d.qtysj,0) AS qtySJ ,
					 ifnull(e.sisaso,0) AS sisaso, ifnull(f.osmod,0) AS sisamod, ifnull(g.qtysjm,0) AS sjm
					FROM (SELECT distinct concat(concat(b.name,' '),c.name) AS name FROM items a LEFT JOIN types b ON a.type_id=b.id
					LEFT JOIN varieties c ON c.id=a.variety_id
					WHERE is_sales_item=1 AND a.type_id IS NOT null )a LEFT JOIN (

					SELECT concat(concat(d.name,' '),e.name) AS tipe, coalesce(SUM(b.qty*b.qty_conversion),0) AS qtySO FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties e ON e.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY concat(CONCAT(d.name,' '),e.name))b ON a.name=b.tipe
               LEFT JOIN (

               SELECT concat(concat(d.name,' '),f.name) AS tipe, coalesce(SUM(b.qty*e.qty_conversion),0) AS qtyMOD
					FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id
					LEFT JOIN marketing_order_details e ON e.id=b.marketing_order_detail_id
					LEFT JOIN items c ON c.id=b.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties f ON f.id=c.variety_id

					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY concat(concat(d.name,' '),f.name)
					)c ON c.tipe=a.name
					LEFT JOIN (
					SELECT concat(concat(d.name,' '),g.name) AS tipe, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN varieties g ON g.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
               GROUP BY concat(concat(d.name,' '),g.name)
					)d ON d.tipe=a.name
					LEFT JOIN (
					SELECT concat(concat(e.name,' '),f.name) AS tipe,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id
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
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id
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
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
						LEFT JOIN varieties h ON h.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY concat(concat(d.name,' '),h.name)
					)g ON g.tipe=a.name
					
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
			'qtyso' => $sodaily,
			'qtymod' => $moddaily,
			'qtysj' => $sjdaily,
			'sisaso' => $osso,
			'sisamod' => $osmod,
			'sjm' => $sjmtd,

		];

		foreach ($query as $row) {

			$data2[] = [
				'name'  => $row->name,
				'qtyso'  => $row->qtySO,
				'qtymod'  => $row->qtyMOD,
				'qtysj'  => $row->qtySJ,
				'sisaso'  => $row->sisaso,
				'sisamod'  => $row->sisamod,
				'sjm'  => $row->sjm,

			];
		}


		//global 1c
		$query = DB::select("		SELECT a.`name` AS tipe, b.`name` AS brand, IFNULL(c.sisaso,0) AS osso, IFNULL(d.osmod,0) AS osmod
							, IFNULL(e.qtysjm,0) AS sj from types a cross JOIN brands b LEFT JOIN  
	(
	SELECT e.name AS tipe, f.`name` AS brand,  SUM((b.qty*b.qty_conversion) - c.sokepakai) AS sisaso
					FROM marketing_orders a
					LEFT JOIN marketing_order_details b ON a.id=b.marketing_order_id
					LEFT JOIN (SELECT c.id, SUM(b.qty * c.qty_conversion) AS sokepakai FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id AND c.deleted_at IS null
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL GROUP BY c.id
					)c ON c.id=b.id
					LEFT JOIN items d ON d.id=b.item_id
					LEFT JOIN types e ON e.id=d.type_id
				LEFT JOIN brands f ON f.id=d.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL
					AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					   GROUP BY e.name,f.name)c ON a.name=c.tipe AND b.name = c.brand
					   LEFT JOIN (
						SELECT  f.name AS tipe,g.name AS brand, SUM(b.qty*c.qty_conversion) AS 'osmod' FROM marketing_order_deliveries a
					LEFT JOIN marketing_order_delivery_details b ON a.id=b.marketing_order_delivery_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_details c ON c.id=b.marketing_order_detail_id AND c.deleted_AT IS null
					LEFT JOIN marketing_order_delivery_process_details d ON d.marketing_order_delivery_detail_id=b.id
					LEFT JOIN items e ON e.id=b.item_id
					LEFT JOIN types f ON f.id=e.type_id
				LEFT JOIN brands g ON g.id=e.brand_id
					WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
					AND d.id IS null
						GROUP BY f.name,g.name
								)d ON a.name=d.tipe AND b.name = d.brand
								LEFT JOIN (
								SELECT d.name AS tipe,g.name AS brand, coalesce(SUM(b.qty*f.qty_conversion),0) AS qtySJm
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id AND e.deleted_at IS null
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
							LEFT JOIN brands g ON g.id=c.brand_id
					WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01') AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
               	 GROUP BY d.name,g.name
						)e ON a.name=e.tipe AND b.name = e.brand 	ORDER BY b.name");

		foreach ($query as $row) {

			$data3[] = [
				'tipe'  => $row->tipe,
				'brand'  => $row->brand,
				'osso'  => $row->osso,
				'osmod'  => $row->osmod,
				'sj'  => $row->sj,


			];
		}

		//global 2a
		$query = DB::select("		   SELECT concat(concat(tipe,' ') ,jenis) AS jenis,brand, SUM(qty) AS endstock FROM (
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis, coalesce(SUM(b.qty*c.conversion),0) AS Qty
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
					 LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
					 LEFT JOIN items d ON d.id=b.item_id
					  INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis, coalesce(SUM(b.qty),0)*-1 AS RepackOut
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id
                LEFT JOIN item_units c ON c.id=item_unit_source_id
                LEFT JOIN items d ON d.id=b.item_source_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis, coalesce(SUM(b.qty),0) AS RepackIn
					 FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id
                LEFT JOIN item_units c ON c.id=item_unit_target_id
                LEFT JOIN items d ON d.id=b.item_target_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS NULL
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis, coalesce(SUM(b.qty),0) AS GR
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id
                LEFT JOIN items d ON d.id=b.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name,g.name
                UNION ALL
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis, coalesce(SUM(c.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
                INNER JOIN types e ON e.id=d.type_id
                INNER JOIN brands f ON f.id=d.brand_id
                INNER JOIN varieties g ON g.id=d.variety_id
					 WHERE a.void_date IS NULL AND a.deleted_at IS null
                GROUP BY e.name,f.name,g.name
                UNION ALL
               SELECT d.`name` AS tipe,g.name AS brand,h.name AS jenis, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
					FROM marketing_order_delivery_processes a
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id
					LEFT JOIN items c ON c.id=e.item_id
					LEFT JOIN types d ON d.id=c.type_id
					LEFT JOIN brands g ON g.id=c.brand_id
					LEFT JOIN varieties h ON h.id=c.variety_id
					WHERE a.void_date is null AND a.deleted_at is NULL
               GROUP BY d.name,g.`name`,h.name)a GROUP BY tipe,brand,jenis");


		foreach ($query as $row) {

			$data4[] = [
				'jenis'  => $row->jenis,
				'brand'  => $row->brand,
				'endstock'  => $row->endstock,



			];
		}

		//global 2b
		$query = DB::select("	
               SELECT brand ,tipe ,jenis,pattern,grade,CODE, SUM(qty) AS endstock FROM (
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty*c.conversion),0) AS Qty
					 FROM production_handovers a
					 LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
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
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id
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
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id
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
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id
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
                SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(c.qty),0)*-1 AS GI
                FROM good_issues a
                LEFT JOIN good_issue_details b ON a.id=b.good_issue_id
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
					LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
					LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id
					LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id
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



		Mail::to($recipient)->send(new SendMailMarketingOEM($obj, $obj2, $obj3, $obj4, $obj5));

		// }



	}
}
