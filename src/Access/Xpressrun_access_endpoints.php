<?php

namespace Xpressrun\Access;   

use Xpressrun\Xpressrun_db_config;

class Xpressrun_access_endpoints {

    public function __construct(){
            add_action( 'rest_api_init', array($this, 'xpressrun_register_routes'));
    }

    public function xpressrun_register_routes() {
        
        register_rest_route( 'xpressrun/v1', '/access', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'create_access_token'),
            'permission_callback' => array($this, 'check_permission_to_access'),
        )); 

        register_rest_route( 'xpressrun/v1', '/disconnect', array(
            'methods'  => \WP_REST_Server::DELETABLE,
            'callback' => array($this, 'delete_xpressrun_access'), 
            'permission_callback' => array($this, 'check_permission_to_access'),
        ));

        register_rest_route( 'xpressrun/v1', '/orders', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'xpressrun_get_orders'),
            'permission_callback' => array($this, 'check_permission_to_access'),
        ) );

        register_rest_route( 'xpressrun/v1', '/orders', array(
            'methods'  => \WP_REST_Server::DELETABLE,
            'callback' => array($this, 'xpressrun_delete_orders'),
            'permission_callback' => array($this, 'check_permission_to_access'),
        ) );
    }
    
    public function check_permission_to_access($request){
        $check = $this->cheick_autorization_access_api($request);
        if(is_wp_error($check) ) {
            return false;
        }
        return true;
    }

    public function create_access_token($request){
			
        $body_request = json_decode($request->get_body());
        $option_name = 'xpr_send_data_' . get_current_network_id();
        $value = [];
        $response = new \WP_REST_Response();
        if(!empty($body_request->nonce) && !empty($body_request->token)){
            $value['nonces'] = $body_request->nonce;
            $value['xpressrun_api_access'] = $body_request->token;
            $value['store_status'] = 'create';
            update_option($option_name, serialize($value));
            $response->set_data(array('success' => true));
        }else{
            $response->set_data(array('success' => false));
        }
        return $response;
    }

    public function delete_xpressrun_access($request){
        $params = $request->get_params();
        $option_name = 'xpr_send_data_' . get_current_network_id();
        $data =  unserialize(get_option($option_name));
		$store_id = $data["nonces"];
        $response = new \WP_REST_Response();
        if(!empty($params) && !empty($params['nonce'])){
            $bd_store_id = $params['nonce'];
        if($store_id == $bd_store_id){
            update_option($option_name, '');
            $response->set_data(array('success' => true, 'message' => 'store is desconnect'));
            return $response;
        }else{
            return new \WP_Error('woocommerce_rest_api_error', 
            __('id_store not found', 'Woocommerce'), array('status' => 404));
        }
      }
      return new \WP_Error('woocommerce_rest_api_fields_error', 
            __('Required store id', 'Woocommerce'), array('status' => 403));
    }

    protected function cheick_autorization_access_api($request)
    {
        $params = $request->get_params();
        if(empty($params['authorization'])){
            return new \WP_Error('woocommerce_rest_autorization_error', 
            __('Woocommerce_access_token required.', 'Woocommerce'), array('status' => 401));
        }
        $access_token = sanitize_text_field($params['authorization']);
        global $wpdb;
        $table = $wpdb->prefix . 'woocommerce_api_keys';
        $key = $wpdb->get_row($wpdb->prepare("
			SELECT *
			FROM {$table}
			WHERE consumer_key = '%s' 
			LIMIT 1
		", wc_api_hash($access_token)), ARRAY_A);

        if (empty($key)) {
           return new \WP_Error('woocommerce_rest_autorization_error', 
           __('invaliid acces token', 'Woocommerce'), array('status' => 401));
        }
        return $key;
    }

    public function xpressrun_get_orders($request){
        $configdb = new Xpressrun_db_config();
	    $resultat = $configdb->getOrdersXpressrun();
        $response = new \WP_REST_Response();
        $response->set_data(array('data' => $resultat));
        return $response;
    }

    public function xpressrun_delete_orders($request){
        $configdb = new Xpressrun_db_config();
	    $resultat = $configdb->deleteOrdersXpressrun();
        $response = new \WP_REST_Response();
        $response->set_data(array('data' => $resultat));
        return $response;
    }
}