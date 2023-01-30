<?php
declare(strict_types=1);
namespace Xpressrun;
use Xpressrun\Api\Xpressrun_Service;

if (!class_exists('Xpressrun_Shipping_Method')) {
class Xpressrun_Shipping_Method  extends \WC_Shipping_Method {
	protected $token;
	public function __construct() {
		$this->id                 = 'xpressrun';
		$this->method_title       = __( 'Xpressrun (XpressRun Local Delivery)', 'xpressrun' );
		$this->method_description = __( 'Dynamic Shipping Rates at Checkout, by <a href="https://developer.xpressrun.com" target="_blank">Xpressrun</a>', 'xpressrun' );
		$this->init();
		$this->enabled = 'yes';
		$this->title   = __( 'xpressrun Shipping', 'xpressrun' );
		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
	}

	private function init()
	{
		$this->init_form_fields();
		$this->init_settings();
		add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50);
		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
	}
	public static function add_settings_tab($settings_tabs)
        {
            $settings_tabs['shipping&section=xpressrun'] = __('xpressrun', 'xpressrun');
            return $settings_tabs;
        }

	/**
	 * Define settings field for this shipping
	 * @return void
	 * @throws \Exception
	 */
	public function init_form_fields(){

		$option_name = 'xpr_send_data_' . get_current_network_id();
       
		$dataOption = get_option($option_name);
		$token_fields = [];
		if ($dataOption == '') {
			$token_fields['es_oauth_ajax'] = [
				'title' => __('Enable Xpressrun', 'xpressrun'),
				'type' => 'button',
				'description' => __('Click \'Enable\' will redirect you to Xpressrun to create an account for free, or connect to an existing Xpressrun account.<br/>If it doesn\'t work, don\'t worry, you can always create an account at <a href=`https://developer.xpressrun.com/signup` target="_blank">developer.xpressrun.com</a> to obtain your Access Token, and paste it below.', 'xpressrun'),
				'default' => 'Enable',
			];
			$this->form_fields = array_merge($token_fields, $this->form_fields);
		} else {
			$dataOpt = unserialize($dataOption);
			if($dataOpt['store_status'] == 'register'){
				$user['nonce'] = $dataOpt['nonces']; 
				$user['site_url'] = get_site_url();
				$user['user_info'] = Xpressrun_Registration::getOwnerInfo();
				$user['company_info'] = Xpressrun_Registration::getCompanyInfo();
				$user['endpoint'] = Xpressrun_Registration::get_endpoint();
				$user['token'] =  $dataOpt['nonces'];

				$validation_fields = array(
					'redirect' => array(
						'title' => __('Create Store on Xpressrun', 'xpressrun'),
						'type' => 'button',
						'label' => "Create",
						'custom_attributes' => [
							'data-user' => json_encode($user),
						],
						'description' => __('Click on the button to open your xpressrun store', 'xpressrun'),
						'default' => __('Create','xpressrun'),
					),
				);
				
				$this->form_fields = array_merge($validation_fields, $this->form_fields);
				valid_oauth_action_button_es();
				add_action('admin_enqueue_scripts', 'valid_oauth_action_button_es');
			}
		}
	}
	
	
	/**
	 * @override
	 *
	 * @param array $package
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function calculate_shipping( $package = array()){
		$option_name = 'xpr_send_data_' . get_current_network_id();
		$dataOption = get_option($option_name);
		if(!empty($dataOption)){
			$dataOpt = unserialize($dataOption);
			if($dataOpt['store_status'] == 'create'){
				if(!is_cart() && !is_checkout()){
					$rate = [
						'id' => $this->id,
						'label'          => 'Same day delivery', 
						'cost'           => 0,
					];
					$this->add_rate($rate);
					return $package;
				}
				if (!empty($package)){
						if(!empty(WC()->session->get('estimation_id'))){
							if(WC()->session->get('destination') == $package['destination'] && WC()->session->get('products') == $this->getProduct($package)){
								$this->add_rate(WC()->session->get('rate'));
								return $package;
							}
						}
						$estimation = new Xpressrun_Service();
						try {
							$quote = $estimation->createQuote($this->getProduct($package), $package['destination'], $package['contents_cost']); 
							if(empty($quote) || $quote->error){
								return;
							}
							if (isset($quote->response->price)){
								if($quote->response->price == 0){
									$rate = [
										'id' => $this->id,
										'label'          => "Xpressrun : " . $quote->response->delivery_type ." (Free)", 
										'cost'           => 0,
									];
								}else{
									$rate = [
										'id' => $this->id,
										'label'          => "Xpressrun : " . $quote->response->delivery_type . " ", 
										'cost'           => $quote->response->price,
									];
								}
								$this->add_rate($rate);
								WC()->session->set( 'rate', $rate);
								WC()->session->set( 'estimation_id', $quote->response->id);
								WC()->session->set( 'destination', $package['destination']);
								WC()->session->set( 'products', $this->getProduct($package));
								return $package;
							}
						}catch(\Exception $e){
							die($e->getMessage());
					}
				}
			}
		 }
			return;
		}

	    /**
		 * @param array $package
		 *
		 * @return array
		 */
		public function getProduct(array $package) {
			$products = [];
			foreach ($package['contents'] as $content){
				/** @var WC_Product_Simple $product */
				$product =  $content['data'];
				$productData =  $product->get_data();
				$productData['quantity'] = $content['quantity'];
				$productData['line_total'] = $content['line_total'];
				$products[] = $productData;
			}
			return $products;
		}
	}
}

function valid_oauth_action_button_es()
{
	wp_enqueue_script(
		'valid_oauth_action_button_es',
		plugin_dir_url(__DIR__) . 'src/assets/js/admin/ajax_oauth_es.js'
	);
}







