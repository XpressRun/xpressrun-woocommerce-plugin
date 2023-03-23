<?php
require_once 'vendor/autoload.php';
/**
	* @package WooCommerce
	* Plugin Name: XpressRun-Local-Delivery
	* Description: Your shipping method plugin
	* Version: 1.0.0
	* Author: XpressRun
    * Developer: Mamadou Soko, Souleymane Ouattara
	* Author URI: https://xpressrun.com
**/
use Xpressrun\Xpressrun_Registration;
use Xpressrun\Xpressrun_db_config;
use Xpressrun\Api\Xpressrun_Service;
use Xpressrun\Entities\Xpressrun_order_entity;
use Xpressrun\Access\Xpressrun_access_endpoints;

if ( ! defined( 'WPINC' ) ){
	die('security by preventing any direct access to your plugin file');
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	if ( class_exists( "WC_Shipping_Method" ) && ! class_exists( 'WC_Integration_Xpressrun' ) ) {

		class WC_Integration_Xpressrun
		{   
			public function __construct()
			{
				add_action('init', array($this, 'createApiConnectors'));
				add_action('init', array($this, 'createDatabaseXp'));
				add_action('woocommerce_shipping_init', array( $this, 'init' ) );
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'xpressrun_plugin_action_links'));
				add_action('wp_ajax_xpr_oauth_es', array($this, 'xpressrun_registration_request'));
			}

			/**
			 * Initialize the plugin.
			 */
			public function init()
			{
				add_filter('woocommerce_shipping_methods', array($this, 'add_integration'));
				add_filter('woocommerce_shipping_methods', array($this, 'add_shipping_method'));
			}

			/** 
			 *
			 */
			public function createApiConnectors(){
				$xp_access_endpoints = new Xpressrun_access_endpoints();
			}
			
			/**
			 * Add a new integration to WooCommerce.
			 * @param $methods
			 *
			 * @return mixed
			 */
			function add_integration($methods)
			{
				$methods[] = 'Xpressrun\Xpressrun_Shipping_Method';
				return $methods;
			}
			/**
			 * 
			 */
			public function add_shipping_method($methods)
			{
				if (is_array($methods)) {
					$methods['xpressrun'] = 'Xpressrun\Xpressrun_Shipping_Method';
				}
				return $methods;
			}
			
			/**
			 *  
			 */
			public function xpressrun_plugin_action_links($links)
			{
				return array_merge(
					$links,
					array('<a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&section=xpressrun') . '"> ' . __('Settings', 'xpressrun') . '</a>')
				);
			}

			public function xpressrun_registration_request()
            {
				$registration = new Xpressrun_Registration();
                $registration->sendRegistrationRequest();
            } 

			public function createDatabaseXp(){
				$configdb = new Xpressrun_db_config();
				$configdb->createOrderTable();
			}

		}

		$WC_Integration_Xpressrun = new WC_Integration_Xpressrun();

		function my_oauth_action_button_es()
		{
			wp_enqueue_script(
				'my_oauth_action_button_es',
				plugin_dir_url(__FILE__) . 'assets/js/admin/ajax_oauth_es.js',
				array('jquery'),
				'5.0.4');
		}

		add_action('admin_enqueue_scripts', 'my_oauth_action_button_es');
		
		function my_validate_order( $posted )   {

			$shipping = $posted["shipping_method"];
			
			if($shipping[0] == 'xpressrun'){
				WC()->session->set('receiver_first_name', $posted["shipping_first_name"]);
				WC()->session->set('receiver_last_name', $posted["shipping_last_name"]);
				WC()->session->set('phone_number', $posted["billing_phone"]); 
				if(!empty($posted["order_comments"]) && strlen($posted["order_comments"]) > 0){
					WC()->session->set('note', $posted["order_comments"]);
				}else{
					WC()->session->set('note', "NA");
				}
				WC()->session->set('shipping_methode', 'xpressrun');
			}
		}

		add_action( 'woocommerce_after_checkout_validation', 'my_validate_order' , 10 );

		function order_status_has_changed_xp( $order_id, $old_status, $new_status ) {
			$order = wc_get_order( $order_id );

			if($old_status === 'pending' && $new_status === 'processing'){
				$shpping = WC()->session->get('shipping_methode');
				if(($shpping == 'xpressrun')){
					$estimation_id = WC()->session->get('estimation_id');
					$first_name = WC()->session->get('receiver_first_name');
					$last_name = WC()->session->get('receiver_last_name');
					$phone_number = WC()->session->get('phone_number');
					$note = WC()->session->get('note');

					$order_id =  strval($order_id)."-".strval(rand(100, 100000));

					$xpress_order = new Xpressrun_order_entity($estimation_id, $phone_number, $first_name, $last_name, $order_id);

					if(!empty($order->data["shipping"]["phone"]) && strlen($order->data["shipping"]["phone"]) > 0){
						$xpress_order->setReceiver_phone_number($order->data["shipping"]["phone"]);
					}

					$order_service = new Xpressrun_Service();
					$response = $order_service->createOrder($xpress_order);

					if($response->error){
						$configdb = new Xpressrun_db_config();
						$resultat = $configdb->addOrder($xpress_order);
					}
				}
			}
		}
		add_action('woocommerce_order_status_changed','order_status_has_changed_xp', 10, 3);
		}
}



