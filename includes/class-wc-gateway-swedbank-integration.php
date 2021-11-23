<?php
/**
 *
 * @package  WC_Gateway_Swedbank_Integration
 * @author   Darius Augaitis
 */

    class WC_Gateway_Swedbank_Integration  {
        private $order;
        private $swMod;

        public function __construct($order, $swMod) {
            global $woocommerce;

            $this->order = $order;
            $this->swMod = $swMod;

        }


        public function getOrder()
        {
            return $this->order;
        }


        public function getSwMod()
        {
            return $this->swMod;
        }

        public function get_url($home_url){
           if($this->swMod->id === 'swedbank_v2_card_lt' || $this->swMod->id === 'swedbank_v2_card_lv' || $this->swMod->id === 'swedbank_v2_card_ee'){
                include 'hps.php';
                $ob =  new swedbank_v2_hps($this->getOrder(), $this->getSwMod(), $home_url);
                return $ob->setupCon();
           } else {
               include 'banklink.php';
                $ob =  new swedbank_v2_banklink($this->getOrder(), $this->getSwMod(), $home_url);
                return $ob->setupCon();
           }
         }
         
         
         public function getDone(){
             include 'hps.php';
             $ob =  new swedbank_v2_hps($this->getOrder(), $this->getSwMod(), '');
             return $ob->complyte();
         }
         
         public function getDoneB(){
             include 'banklink.php';
             $ob =  new swedbank_v2_banklink($this->getOrder(), $this->getSwMod(), '');
             return $ob->complyte();
         }



    }
