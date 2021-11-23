<?php

/**
 *
 * @author
 */
include_once 'logger.php';

class swedbank_v2_banklink
{

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
    private $pType;
    private $re;

    public function __construct($order = null, $config = null, $backUrl = null, $price = null, $lng = null, $env = null, $id = null, $home_url = null, $shopLng = null, $pType, $re = null)
    {
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
        $this->pType = $pType.'_'.$lng;
        $this->re = $re;
    }

    public function setupCon()
    {

        $vtid = $this->env ? $this->config->get('swedbank_vtid_' . $this->lng) : $this->config->get('swedbank_testvtid_' . $this->lng);
        $psw = $this->env ? $this->config->get('swedbank_pass_' . $this->lng) : $this->config->get('swedbank_testpass_' . $this->lng);

        $merchantReferenceId =  $this->merchantRef;

        if ($this->pType === 'swpaymentsw_lt') {
            $paymentmethod = 'SW';
            $serviceType = '<ServiceType>LIT_BANK</ServiceType>';
        } else if ($this->pType === 'swpaymentseb_lt') {
            $paymentmethod = 'SE';
            $serviceType = '<ServiceType>SEB_LIT</ServiceType>';
        } else if ($this->pType === 'swpaymentdnb_lt') {
            $paymentmethod = 'DN';
            $serviceType = '';
        } else if ($this->pType === 'swpaymentsw_lv') {
            $paymentmethod = 'SW';
            $serviceType = '<ServiceType>LTV_BANK</ServiceType>';
        } else if ($this->pType === 'swpaymentseb_lv') {
            $paymentmethod = 'SE';
            $serviceType = '<ServiceType>SEB_LTV</ServiceType>';
        } else if ($this->pType === 'swpaymentsw_ee') {
            $paymentmethod = 'SW';
            $serviceType = '<ServiceType>EST_BANK</ServiceType>';
        } else {
            return false;
        }

        $return_url = $this->home_url . '?swedbankv2=done&amp;order_id=' . $merchantReferenceId . '&amp;pmmm='.$this->pType.'&amp;t=' . $this->env . '&amp;lnv=' . $this->lng.'&amp;re='.$this->re; // return url
        $error_url = $this->home_url . '?swedbankv2=done&amp;order_id=' . $merchantReferenceId . '&amp;pmmm='.$this->pType.'&amp;t=' . $this->env . '&amp;lnv=' . $this->lng.'&amp;re='.$this->re; // error url

        $xml = <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<Request version="2">
   <Authentication>
      <client>{$vtid}</client>
      <password>{$psw}</password>
   </Authentication>
   <Transaction>
    <TxnDetails>
      <merchantreference>{$this->re}</merchantreference>
    </TxnDetails>
    <HpsTxn>
      <page_set_id>1</page_set_id>
      <method>setup_full</method>
    </HpsTxn>
    <APMTxns>
      <APMTxn>
        <method>purchase</method>
        <payment_method>{$paymentmethod}</payment_method>
        <AlternativePayment version="2">
          <TransactionDetails>
            <Description>Order nr: {$this->re}</Description>
            <SuccessURL>{$return_url}</SuccessURL>
            <FailureURL>{$error_url}</FailureURL>
            <Language>{$this->shopLng->iso_code}</Language>
            <PersonalDetails>
                <Email>{$this->order->email}</Email>
            </PersonalDetails>
            <BillingDetails>
              <AmountDetails>
                <Amount>{$this->price}</Amount>
                <Exponent>2</Exponent>
                <CurrencyCode>978</CurrencyCode>
              </AmountDetails>
            </BillingDetails>
          </TransactionDetails>
          <MethodDetails>
            {$serviceType}
          </MethodDetails>
        </AlternativePayment>
      </APMTxn>
    </APMTxns>
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

            $url = ((string)$object->HpsTxn->hps_url[0]) . '?HPS_SessionID=' . ((string)$object->HpsTxn->session_id[0]);
        } else
            return false;

        include __DIR__ . '/../src/Entity/SwedbankOrderStatus.php';

        $paymentStatus = new SwedbankOrderStatus();
        $paymentStatus->insertItem($this->merchantRef, 0, $merchantReferenceId, $this->pType, $this->env, $this->lng );

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

            return [(int)$object->QueryTxnResult->status[0]];
        } else
            return false;
    }

    function curOp($envUrl, $xml)
    {
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
