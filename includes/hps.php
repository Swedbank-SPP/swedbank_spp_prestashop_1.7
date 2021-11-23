<?php

/**
 *
 * @author   
 */
include_once 'logger.php';

class swedbank_v2_hps {

    private $order;
    private $home_url;
    private $log;
    private $price;
    private $config;
    private $lng;
    private $env;
    private $merchantRef;
    private $backUrl;
    private $shopLng;
    private $re;

    public function __construct($order = null, $config = null, $backUrl = null, $price = null, $lng = null, $env = null, $id = null, $home_url = null, $shopLng = null, $re = null) {
        $this->order = $order;
        $this->home_url = $home_url;
        //if(!class_exists('Swedbank_Client_Logger'))
        $this->log = new Swedbank_Client_Logger();
        $this->price = $price;
        $this->config = $config;
        $this->lng = $lng;
        $this->env = $env;
        $this->merchantRef = $id;
        $this->backUrl = $backUrl;
        $this->shopLng = $shopLng;
        $this->re = $re;
    }

    public function setupCon() {

        $vtid = $this->env ? $this->config->get('swedbank_vtid_'.$this->lng) : $this->config->get('swedbank_testvtid_'.$this->lng);
        $psw = $this->env ? $this->config->get('swedbank_pass_'.$this->lng) : $this->config->get('swedbank_testpass_'.$this->lng);

        $merchantReferenceId = $this->merchantRef;

        $expire_url = $this->home_url . '?swedbankv2=done&amp;order_id=' . $merchantReferenceId . '&amp;pmmm=c&amp;t='.$this->env.'&amp;lnv='.$this->lng.'&amp;re='.$this->re; // expire url
        $return_url = $this->home_url . '?swedbankv2=done&amp;order_id=' . $merchantReferenceId . '&amp;pmmm=c&amp;t='.$this->env.'&amp;lnv='.$this->lng.'&amp;re='.$this->re; // return url
        $error_url = $this->home_url . '?swedbankv2=done&amp;order_id=' . $merchantReferenceId . '&amp;pmmm=c&amp;t='.$this->env.'&amp;lnv='.$this->lng.'&amp;re='.$this->re; // error url

        $page_set_id = $this->env ? '4018' : '329';

        $date = date('Ymd H:i:s');

        //echo '<pre>'; //countries[id_country
        //print_r($this->order->countries[$this->order->id_country]['iso_code']); die;

        $xml = <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2">
   <Authentication>
      <client>{$vtid}</client>
      <password>{$psw}</password>
   </Authentication>
   <Transaction>
      <TxnDetails>
      <Risk>
        <Action service="1">
        <MerchantConfiguration>
          <channel>W</channel>
         </MerchantConfiguration>
        <CustomerDetails>
          <OrderDetails>
            <BillingDetails>
              <state_province></state_province>
              <name>{$this->order->firstname} {$this->order->lastname}</name>
              <address_line1>{$this->order->address1}</address_line1>
              <address_line2>{$this->order->address2}</address_line2>
              <city>{$this->order->city}</city>
              <zip_code>{$this->order->postcode}</zip_code>
              <country>{$this->order->countries[$this->order->id_country]['iso_code']}</country>
            </BillingDetails>
          </OrderDetails>
          <PersonalDetails>
             <first_name>{$this->order->firstname}</first_name>
             <surname>{$this->order->lastname}</surname>
             <telephone>{$this->order->phone}</telephone>
          </PersonalDetails>
          <ShippingDetails>
            <title></title>
            <first_name>{$this->order->firstname}</first_name>
            <surname>{$this->order->lastname}</surname>
            <address_line1>{$this->order->address1}</address_line1>
            <address_line2>{$this->order->address2}</address_line2>
            <city>{$this->order->city}</city>
            <country>{$this->order->countries[$this->order->id_country]['iso_code']}</country>
            <zip_code>{$this->order->postcode}</zip_code>
          </ShippingDetails>
          <PaymentDetails>
            <payment_method>CC</payment_method>
          </PaymentDetails>
          <RiskDetails>
            <email_address>{$this->order->email}</email_address>
            <ip_address>{$this->order->ip}</ip_address>
          </RiskDetails>
        </CustomerDetails>
      </Action>
     </Risk>
     <merchantreference>{$this->re}</merchantreference>
     <ThreeDSecure>
        <purchase_datetime>{$date}</purchase_datetime>
        <merchant_url>{$this->home_url}</merchant_url>
        <purchase_desc>Reference nr: {$this->re}</purchase_desc>
        <verify>yes</verify>
     </ThreeDSecure>
     <capturemethod>ecomm</capturemethod>
     <amount currency="EUR">{$this->price}</amount>
   </TxnDetails>
   <HpsTxn>
     <method>setup_full</method>
     <page_set_id>{$page_set_id}</page_set_id>
     <return_url>{$return_url}</return_url>
     <expiry_url>{$expire_url}</expiry_url>
     <error_url>{$error_url}</error_url>
     <DynamicData>
    <dyn_data_3></dyn_data_3>
    <dyn_data_4>{$this->backUrl}</dyn_data_4>
        <dyn_data_5>{$this->shopLng->iso_code}</dyn_data_5>
    <dyn_data_6>visaelectron_maestro_visa_mastercard</dyn_data_6>
    <dyn_data_7></dyn_data_7>
    <dyn_data_8></dyn_data_8>
    <dyn_data_9></dyn_data_9>
</DynamicData>
   </HpsTxn>
   <CardTxn>
      <method>auth</method>
   </CardTxn>
</Transaction>
</Request>

EOL;

        //echo '<pre>';
        //print_r($xml); die;

        $this->config->get('swedbank_debuging') ? $this->log->logData($xml) : null;

        $xml = $this->curOp(!$this->env ? 'https://accreditation.datacash.com/Transaction/acq_a' : 'https://mars.transaction.datacash.com/Transaction', $xml);

        $this->config->get('swedbank_debuging') ? $this->log->logData($xml) : null;

        try {
            $object = new SimpleXMLElement($xml);
        } catch (Exception $exc) {
            $this->config->get('swedbank_debuging') ? $this->log->logData('Failed parse xml') : null;
            return false;
        }

        if ((int) $object->status === 1) {

            $url = ((string) $object->HpsTxn->hps_url[0]) . '?HPS_SessionID=' . ((string) $object->HpsTxn->session_id[0]);
        } else
            return false;

        include __DIR__ . '/../src/Entity/SwedbankOrderStatus.php';

        $paymentStatus = new SwedbankOrderStatus();
        $paymentStatus->insertItem($this->merchantRef, 0, $merchantReferenceId, 'c', $this->env, $this->lng );

        return $url;
    }

    public function complyte()
    {
        $vtid = $this->env ? $this->config->get('swedbank_vtid_' . $this->lng) : $this->config->get('swedbank_testvtid_' . $this->lng);
        $psw = $this->env ? $this->config->get('swedbank_pass_' . $this->lng) : $this->config->get('swedbank_testpass_' . $this->lng);

        $xml = <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2">
   <Authentication>
      <client>{$vtid}</client>
      <password>{$psw}</password>
   </Authentication>
   <Transaction>
    <HistoricTxn>
        <method>query</method>
        <reference type="merchant">{$this->re}</reference>
    </HistoricTxn>
  </Transaction>
</Request>

EOL;

        $this->config->get('swedbank_debuging') ? $this->log->logData($xml) : null;

        $xml = $this->curOp(!$this->env ? 'https://accreditation.datacash.com/Transaction/acq_a' : 'https://mars.transaction.datacash.com/Transaction', $xml);

        $this->config->get('swedbank_debuging') ? $this->log->logData($xml) : null;

        try {
            $object = new SimpleXMLElement($xml);
        } catch (Exception $exc) {
            $this->config->get('swedbank_debuging') ? $this->log->logData('Failed parse xml') : null;
            return false;
        }

        if ((int)$object->status === 1) {
            $r['status'] = 'SUCCESS';
            //authcode
            $r['authcode'] = $object->QueryTxnResult->authcode[0]->__toString();
            //pan
            $r['pan'] = $object->QueryTxnResult->Card->pan[0]->__toString();
            $r['ex'] = $object->QueryTxnResult->Card->expirydate[0]->__toString();
            $r['fulfill_date'] = $object->QueryTxnResult->fulfill_date[0]->__toString();
            $r['merchant_reference'] = $object->QueryTxnResult->merchant_reference[0]->__toString();

            return [((int)$object->QueryTxnResult->status[0]), $r];
        } else
            return false;

    }

    function curOp($envUrl, $xml) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $envUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.12) Gecko/2009070611 Firefox/3.0.12");


        //print_r($xml); die;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $respond = curl_exec($ch);
        curl_close($ch);
        return $respond;
    }

}
