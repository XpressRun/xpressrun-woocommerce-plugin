<?php

declare(strict_types=1);

namespace Xpressrun\Api;

use Xpressrun\Entities\Xpressrun_order_entity;
use Exception;
use WC_Product_Simple;

class Xpressrun_Service extends Xpressrun_Abstract_Api {
	/**
	 * @param array $package
	 *
	 * @return mixed|void
	 * @throws Exception
	 */
	public function createQuote( $products, $destination, $contents_cost){
		
		$option_name = 'xpr_send_data_' . get_current_network_id();
		$xp_option = get_option($option_name);
		$store_id = null;
		$api_key = null;
		if(!empty($xp_option)){
			$data =  unserialize(get_option($option_name));
		    $store_id = $data["nonces"];
		    $api_key = $data['xpressrun_api_access']; 
		}
			$payload = 	$this->generatePayloadForEstimation($products, $destination, $contents_cost);

			$response = $this->postEstimation("/v1/ecommerce/estimate", $payload, $api_key);
	    	$response = json_decode($response);
		    return $response;
	}

	/**
	 * \[
	 * @param array $package
	 *
	 * @return mixed|void
	 * @throws Exception
	 */
	public function createOrder( Xpressrun_order_entity $order){

		$option_name = 'xpr_send_data_' . get_current_network_id();
		$xp_option = get_option($option_name);
		$api_key = null;
		if(!empty($xp_option)){
			$data =  unserialize(get_option($option_name));
		    $api_key = $data['xpressrun_api_access']; 
		}

		$payload = 	$this->generatePayloadForCreateOrder($order);

		$response = $this->postOrder("/v1/ecommerce/order", $payload, $api_key);
		$response = json_decode($response);

		return $response;
	}

	private function generatePayloadForEstimation( array $products, array $destination,float $contents_cost ) {
		$items = array();
		for($i=0; $i < count($products); $i++){
					$items[] = [
						    "variant_id" => $products[$i]['id'],
						    "name" => $products[$i]['name'],
							"product_id" => $products[$i]['parent_id'] != 0 ? $products[$i]['parent_id'] : $products[$i]['id'],
							"length" => (float)$products[$i]['length'],
							"width" => (float)$products[$i]['width'],
							"height" => (float)$products[$i]['height'],
							"weight" => (float)$products[$i]['weight'],
							"value" => (int) $products[$i]['line_total'],
							"quantity" => (int) $products[$i]['quantity'],
					];
		}
		return [
				"package_type" => "LARGE",
				"order_amount" => $contents_cost,
				"manifest" => [
					"name" => "WooCommerce Order",
					"description"=> "WooCommerce order",
					"order_total" => $contents_cost,
					"manifest_items" => $items
				],
				'dropoff_information' => [
					'address' => [
						'address_name' => $destination['address_1'] .', '. $destination['city']  .', '. "USA",
						'state' => $destination['state'],
						'address_1' => $destination['address_1'],
						"city" => $destination['city'],
						"country"=> "USA",
						"zip_code"=> $destination['postcode'],
						"address_2"=> $destination['address_2'],
					]
				]
			];
		}

	private function generatePayloadForCreateOrder(Xpressrun_order_entity $Order) {
		return [
				"estimation_id" => $Order->getEstimation_id(),
				"dropoff_information" => [
					"receiver" => [
						"name" => $Order->getReceiver_full_name(),
					    "phone_number"=> $Order->getReceiver_phone_number(),
					],
					"note" => $Order->getNote(),
				],
				"external_order_id" => $Order->getExternal_order_id()
			];
		}
}