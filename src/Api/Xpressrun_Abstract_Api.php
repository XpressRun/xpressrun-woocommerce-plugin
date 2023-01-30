<?php

declare(strict_types=1);

namespace Xpressrun\Api;

use Exception;
use stdClass;
use const true;

abstract class Xpressrun_Abstract_Api {

	protected $baseUrl = "https://openapi.xpressrun.com";
	private $check_product_endpoint = 'https://developer.xpressrun.com/integrations/woocommerce/products/';
	                                   
	        
	public function __construct() {}
	/**
	 * @throws Exception
	 */
	protected function postEstimation(string $urlSuffix, array $requestArray, string $token){

		$response = wp_remote_post( $this->baseUrl . $urlSuffix, array(
			'headers' => [
			'Content-type' => 'Application/json',
			'X-OPEN-API' => $token
		],
			'body' => json_encode($requestArray),
			'method' => 'POST'
		));

		if (is_wp_error($response)){
		  return json_encode(array ( 
			"code" => 502,
			"error" => true,
			"message" =>  $response->get_error_message(),
			"response" => null));
		}

		if ( is_array($response['response']) && !is_wp_error($response) ) {
			if (!empty($response['response']['code'])){

				if($response['response']['code'] !== 201){
					return json_encode(array ( 
						"code" => $response['response']['code'],
						"error" => true,
						"message" => $response['response']['message'],
						"response" => $response['body']));
				}else{
					if(!empty(json_decode($response['body'])->data)){
						return json_encode(array ( 
							"code" => 201,
							"error" => false,
							"message" => "estimation done successfully",
							"response" => json_decode($response['body'])->data));
					}
					return json_encode(array ( 
						"code" => 202,
						"error" => true,
						"message" => "All Products not available for shipping",
						"response" => null));
				}
			}
		}
	}

	protected function postOrder(string $urlSuffix, array $requestArray, string $token){
		$response = wp_remote_post( $this->baseUrl . $urlSuffix, array(
			'headers' => [
			'Content-type' => 'Application/json',
            'X-OPEN-API' => $token,
		],
			'body' => json_encode($requestArray),
			'method' => 'POST'
		));

		if (is_wp_error($response)){
		  return json_encode(array ( 
			"code" => 502,
			"error" => true,
			"message" =>  $response->get_error_message(),
			"response" => null));
		}

		if ( is_array($response['response']) && !is_wp_error($response) ) {
			if (!empty($response['response']['code'])){
				if($response['response']['code'] !== 201){
						return json_encode(array ( 
							"code" => $response['response']['code'],
							"error" => true,
							"message" => $response['response']['message'],
							"response" => null));
				}else{
					return json_encode(array ( 
						"code" => 201,
						"error" => false,
						"message" => "Order create successfully",
						"response" => json_decode($response['body'])->data));
				}
			}
		}
	}
}

