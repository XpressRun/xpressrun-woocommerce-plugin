<?php

namespace Xpressrun;

if (!class_exists('Xpressrun_Registration')) {

    class Xpressrun_Registration
    {
        private $wpdb;
        private $woocommerce;            
        private $registration_endpoint = 'https://developer.xpressrun.com/integrations/woocommerce/registration';
        protected static $endpoint = 'https://app.developer.xpressrun.com/integration/check';
                         
        public function __construct()
        {
            global $wpdb;
            global $woocommerce;
            $this->wpdb = $wpdb;
            $this->woocommerce = $woocommerce;
        }

        /**
	     * @return string
	     */
	    public static function get_endpoint() {
		    return self::$endpoint;
	    }

        public function sendRegistrationRequest()
        {
            $option_name = 'xpr_send_data_' . get_current_network_id();
            $request = [];
            $request['access'] = $this->generate_token_for_registration();
            $request['owner'] = $this->getOwnerInfo();
            $request['company'] = $this->getCompanyInfo();
            $request['store'] = $this->getStoreInfo();
            $request['address'] = $this->getAddressInfo();

            $response = wp_remote_post($this->registration_endpoint, array(
                'headers' => [
                'Content-type' => 'Application/json',
            ],
                'body' => json_encode($request),
                'method' => 'POST'
            ));
            if (is_wp_error($response)){
                return;
              }

            if ( is_array($response['response']) && !is_wp_error($response) ) {
                if (!empty($response['response']['code'])){
                    if($response['response']['code'] !== 201){
                            return json_encode(['error' => 'Service temporarily unavailable']);
                    }else{
                        if (empty(json_decode($response['body'])->data->result)) {
                            update_option($option_name, '');
                            return json_encode(['error' => 'Service temporarily unavailable']);
                        }
                        $value = [];
                        $value['store_status'] = 'register';
                        $value['nonces'] = $request['access']['nonce'];
                        update_option($option_name, serialize($value));
                        return json_encode(['response' => $response]);
                    }
                }
            }
        }

        /**
         * @return array 
         */
		public function generate_token_for_registration(){
            $xp_nonces = 'xpress-'.$this->uuidv4().'-run';
            $customer_key = 'ck_' . wc_rand_hash();
            $customer_secret = 'cs_' . wc_rand_hash();
            $table = $this->wpdb->prefix . 'woocommerce_api_keys';
            $data = array(
                'user_id' => get_current_user_id(),
                'description' => 'Xpressrun Integration WC',
                'permissions' => 'read_write',
                'consumer_key' => wc_api_hash($customer_key),
                'consumer_secret' => $customer_secret,
                'truncated_key' => substr($customer_key, -7),
            );
            $this->wpdb->query("DELETE FROM $table WHERE description = 'Xpressrun Integration WC'");
            $this->wpdb->insert($table, $data, array('%d','%s','%s','%s','%s','%s',));
            return ['consumer_key' => $customer_key, 'consumer_secret' => $customer_secret, 'nonce' => $xp_nonces];
		}

        static function getOwnerInfo()
        {
            $user = wp_get_current_user();
            $response['email'] = $user->user_email;
            $response['first_name'] = 'default';
            $response['last_name'] = 'default';
            $response['mobile_phone'] = !empty($user->billing_phone) ? $user->billing_phone : '';
            return $response;
        }

        static function getCompanyInfo()
        {
            $response = array();
            $country = explode(':', get_option('woocommerce_default_country'));
            $response['name'] = get_option('blogname');
            $response['country_code'] = !empty($country[0]) ? $country[0] : '';
            return $response;
        }

        protected function getStoreInfo()
        {
            $response = array();
            $response['platform_store_id'] = get_current_network_id();
            $response['name'] = get_option('blogname');
            $response['url'] = get_option('home');
            $response['wc_version'] = $this->woocommerce->version;
            return $response;
        }

        protected function getAddressInfo()
        {
            $response = array();
            $country = explode(':', get_option('woocommerce_default_country'));
            $city = get_option('woocommerce_store_city');
            $postal_code = get_option('woocommerce_store_postcode');
            $line_1 = get_option('woocommerce_store_address');
            $line_2 = get_option('woocommerce_store_address_2');

            $response['state'] = !empty($country[1]) ? $country[1] : '';
            $response['city'] = !empty($city) ? $city : '';
            $response['postal_code'] = !empty($postal_code) ? $postal_code : '';
            $response['line_1'] = !empty($line_1) ? $line_1 : '';
            $response['line_2'] = !empty($line_2) ? $line_2 : '';

            return $response;
        }
        /**
         * generating a unique ID
         */
        public function uuidv4($data = null) {
            
            $data = $data == null ? $data : openssl_random_pseudo_bytes(16);
            assert(strlen($data) == 16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
            if(empty($uuid))
            return uniqid();
            return $uuid;
        }
    }
}
