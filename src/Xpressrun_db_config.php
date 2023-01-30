<?php

namespace Xpressrun;

use Xpressrun\Entities\Xpressrun_order_entity;

class Xpressrun_db_config {

    private $wpdb;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function createOrderTable(){

        $table_name = $this->wpdb->prefix . 'xpressrun_order';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            time datetime DEFAULT '0000-00-00 00:00:00',
            external_order_id varchar(15) NOT NULL,
            estimation_id varchar(26) NOT NULL,
            receiver_phone_number varchar(15),
            receiver_full_name varchar(40),
            receiver_email varchar(25),
            note text,
            PRIMARY KEY  (external_order_id)
          ) $charset_collate;";
          
          require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

          dbDelta( $sql );
    }

    public function addOrder(Xpressrun_order_entity $Order){

        $table_name = $this->wpdb->prefix . 'xpressrun_order';
        $sql = "INSERT INTO ".$table_name."(time, external_order_id, estimation_id, receiver_phone_number, receiver_full_name, receiver_email, note) VALUES (%s, %s, %s, %s, %s, %s, %s)";
        $values = array(current_time( 'mysql' ), $Order->getExternal_order_id(), $Order->getEstimation_id(), $Order->getReceiver_phone_number(), $Order->getReceiver_full_name(), $Order->getReceiver_email(), $Order->getNote());
        $query = $this->wpdb->prepare($sql, $values);
        $insert = $this->wpdb->query($query);
        return $insert;
    }

    public function getOrdersXpressrun(){
        $table_name = $this->wpdb->prefix . 'xpressrun_order';
        $query = $this->wpdb->prepare("SELECT * FROM `{$table_name}`");
        $return = $this->wpdb->get_results($query);
        return $return;
    }

    public function deleteOrdersXpressrun(){
        $table_name = $this->wpdb->prefix . 'xpressrun_order';
        $query = $this->wpdb->prepare("DELETE FROM `{$table_name}`");
        $return = $this->wpdb->query($query);
        return $return;
    }

}