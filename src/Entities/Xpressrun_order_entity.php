<?php
declare(strict_types=1);

namespace Xpressrun\Entities;


class Xpressrun_order_entity {

    private $estimation_id;
    private $receiver_email;
    private $receiver_phone_number;
    private $receiver_full_name;
    private $external_order_id;
    private $note;

    public function __construct($id_estimation, $phone_number, $first_name, $last_name, $order_id){
        $this->estimation_id = $id_estimation;
        $this->receiver_phone_number = $phone_number;
        $this->receiver_full_name = $first_name . " " . $last_name;
        $this->external_order_id = $order_id;
        $this->note = "NA";
    }

    public function getEstimation_id(){
        return $this->estimation_id;
    }

    public function setEstimation_id($estimation_id){
        $this->estimation_id = $estimation_id;
    }

    public function getNote(){
        return $this->note;
    }

    public function setReceiver_phone_number($receiver_phone_number){
        $this->receiver_phone_number = $receiver_phone_number;
    }

    public function getReceiver_phone_number(){
        return $this->receiver_phone_number;
    }

    public function getReceiver_full_name(){
        return $this->receiver_full_name;
    }

    public function getExternal_order_id(){
        return $this->external_order_id;
    }

    public function getReceiver_email(){
        return $this->receiver_email;
    }
    
}