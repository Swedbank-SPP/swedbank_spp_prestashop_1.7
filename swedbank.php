<?php


use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
//use DB;
//use ModuleAdminController;
//use OrderState;
//use Collection;
//use Tools;
//use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}


class Swedbank extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;
    private $sw_cer_lt = '-----BEGIN CERTIFICATE-----
MIIEDjCCAvagAwIBAgITVwAAAh/PBfcG6yBi9gAAAAACHzANBgkqhkiG9w0BAQUF
ADBOMQswCQYDVQQGEwJTRTEXMBUGA1UEChMOU3dlZGJhbmsgR3JvdXAxJjAkBgNV
BAMTHVN3ZWRiYW5rIEczIElzc3VpbmcgQ0EgVGllciBBMB4XDTE3MTEwNjA5MTAw
OVoXDTIwMTEwNTA5MTAwOVowODELMAkGA1UEBhMCTFQxETAPBgNVBAoTCFN3ZWRi
YW5rMRYwFAYDVQQDEw1CYW5rbGluayBIb3N0MIGfMA0GCSqGSIb3DQEBAQUAA4GN
ADCBiQKBgQDRaR94bIp05bc/o2ccvWmwuUEfN1WFUPL0wMXN1Wv1rWX68ay7liS/
LBzc3gBq9ungBLlFfaYxBohcJf43gNiZPzdUkBcXJnTeDZxdUzuRuzHA+JOyWqbt
4lcZ4K1l405LJsl5qaXApendeftIN2RpcCK/59Oqyu6thK05JB1HRQIDAQABo4IB
fTCCAXkwHQYDVR0OBBYEFGT0PNTdXiM+/v9Yd2xGD1qDoYdzMB8GA1UdIwQYMBaA
FAihXNOvBjSLItpZ/Qg6KAQ9wIRpMFQGA1UdHwRNMEswSaBHoEWGQ2h0dHA6Ly9p
bmZyYS5zd2VkYmFuay5jb20vcGtpL2NybC9Td2VkYmFua19HM19Jc3N1aW5nX0NB
X1RpZXJfQS5jcmwwXwYIKwYBBQUHAQEEUzBRME8GCCsGAQUFBzAChkNodHRwOi8v
aW5mcmEuc3dlZGJhbmsuY29tL3BraS9jcnQvU3dlZGJhbmtfRzNfSXNzdWluZ19D
QV9UaWVyX0EuY3J0MAsGA1UdDwQEAwIHgDA9BgkrBgEEAYI3FQcEMDAuBiYrBgEE
AYI3FQiD+8FigsjKGYf1jQeCwoAkgebsfGCCo+dNg/3yXAIBZAIBAzAVBgNVHSUE
DjAMBgorBgEEAYI3CgMMMB0GCSsGAQQBgjcVCgQQMA4wDAYKKwYBBAGCNwoDDDAN
BgkqhkiG9w0BAQUFAAOCAQEAPLcOnG0E3TH6w7wNu8TMsHGAy5jVo/KDQIwnFc1r
Wib679AWpNLkW9aiVghs+9xa+7Al2JFO83fbAwnwSCacif4UGodmdj7drAwwINsI
m4QiMBRY0c34FmokUxB88N8G/+qzKLMMZDL7ljEWtz8KZY31If4RTXTylMcLpU1r
2Y9lH/HH+fr+5wDXt/t+ikvbc2tEH6b+rByfjts7CGMXThb9QLRHnz5WwihYHmiC
iaXXIZr5BBYjzTQIgv9GD0JziRvhaD28Oeym394ICzxZJl3XzG25dY2KJNm1HWLu
u8n2e1CNTanCBA1Bv1S4V0OdyidLaTuJZ0y7ODa3+8trFQ==
-----END CERTIFICATE-----';
    private $sw_cer_lv = '-----BEGIN CERTIFICATE-----
MIIEDjCCAvagAwIBAgITVwAAAiAdXtn3OPXGNwAAAAACIDANBgkqhkiG9w0BAQUF
ADBOMQswCQYDVQQGEwJTRTEXMBUGA1UEChMOU3dlZGJhbmsgR3JvdXAxJjAkBgNV
BAMTHVN3ZWRiYW5rIEczIElzc3VpbmcgQ0EgVGllciBBMB4XDTE3MTEwNjA5MTIy
NloXDTIwMTEwNTA5MTIyNlowODELMAkGA1UEBhMCTFYxETAPBgNVBAoTCFN3ZWRi
YW5rMRYwFAYDVQQDEw1CYW5rbGluayBIb3N0MIGfMA0GCSqGSIb3DQEBAQUAA4GN
ADCBiQKBgQDE+w2KupA9quH11ej1NAfczkL7TNmeHynzhNksmmtYtYNAuw3VmUzY
JoKb2o5RoOQ1bizVBKTOKbSIexcLaLrGk/KeOm+jZSDusiF/HXm0rz/pTBmhIG8G
lLCVH7u6E0huJP5scoaQuBtpWur2Y4bneKiETudK2GrrsTYcKdiwYQIDAQABo4IB
fTCCAXkwHQYDVR0OBBYEFFyIMWI8qDLQNzDOMOMMP75WcQDfMB8GA1UdIwQYMBaA
FAihXNOvBjSLItpZ/Qg6KAQ9wIRpMFQGA1UdHwRNMEswSaBHoEWGQ2h0dHA6Ly9p
bmZyYS5zd2VkYmFuay5jb20vcGtpL2NybC9Td2VkYmFua19HM19Jc3N1aW5nX0NB
X1RpZXJfQS5jcmwwXwYIKwYBBQUHAQEEUzBRME8GCCsGAQUFBzAChkNodHRwOi8v
aW5mcmEuc3dlZGJhbmsuY29tL3BraS9jcnQvU3dlZGJhbmtfRzNfSXNzdWluZ19D
QV9UaWVyX0EuY3J0MAsGA1UdDwQEAwIHgDA9BgkrBgEEAYI3FQcEMDAuBiYrBgEE
AYI3FQiD+8FigsjKGYf1jQeCwoAkgebsfGCCo+dNg/3yXAIBZAIBAzAVBgNVHSUE
DjAMBgorBgEEAYI3CgMMMB0GCSsGAQQBgjcVCgQQMA4wDAYKKwYBBAGCNwoDDDAN
BgkqhkiG9w0BAQUFAAOCAQEAUaoqhAEsdng2o0HRcydmg8ktjWo3uaukbHlHRvZH
etCADj5eVlt90ra981AEDAGjJsss1mnVTyQhNLTWmp1B8rS4QzctWn4gzpEfdO77
mI+cCzSdFPmHDMagAb/rAFu+qRsYV7oC+nscvTFLIBrr19ABsQwTMV4krsmHmbTX
rQO24rcJQ+cSwSz02hd8cyuHs7lrOXBtjSGMKyxkXh7ZBGHULjfgXmv8Anp7keAU
L4kd0XhkcpoP68btDIR3/nurIz2QpJbR6Me/+NYxK4NU6DY9aaZAZBZm6lYn/OTi
Dq9rIW1o3bLpig80lD3M/uusx7CzoGfo45YKopEsw5Tklg==
-----END CERTIFICATE-----';
    private $sw_cer_ee = '-----BEGIN CERTIFICATE-----
MIIEDjCCAvagAwIBAgITVwAAAh6+2vl9fqWDTwAAAAACHjANBgkqhkiG9w0BAQUF
ADBOMQswCQYDVQQGEwJTRTEXMBUGA1UEChMOU3dlZGJhbmsgR3JvdXAxJjAkBgNV
BAMTHVN3ZWRiYW5rIEczIElzc3VpbmcgQ0EgVGllciBBMB4XDTE3MTEwNjA5MDc0
NFoXDTIwMTEwNTA5MDc0NFowODELMAkGA1UEBhMCRUUxETAPBgNVBAoTCFN3ZWRi
YW5rMRYwFAYDVQQDEw1CYW5rbGluayBIb3N0MIGfMA0GCSqGSIb3DQEBAQUAA4GN
ADCBiQKBgQCxSrHPphy8fR9ryqnJqXvk8clhTMcUM1ce03mec/8l8VW6Z8I0n5dc
ytAfJogJ03aaNcgxIkft8S1z8cYTDhkMGKKTGpeMntYTQ9eVW1yjWWCWRM6B2U0U
0ezQ44Aysl393t4BCofmSzOUJZQduinojkiIPgqwokpuOfi61E+qjwIDAQABo4IB
fTCCAXkwHQYDVR0OBBYEFPXf1N6RPeXvFb5rdq1uTfCTs04vMB8GA1UdIwQYMBaA
FAihXNOvBjSLItpZ/Qg6KAQ9wIRpMFQGA1UdHwRNMEswSaBHoEWGQ2h0dHA6Ly9p
bmZyYS5zd2VkYmFuay5jb20vcGtpL2NybC9Td2VkYmFua19HM19Jc3N1aW5nX0NB
X1RpZXJfQS5jcmwwXwYIKwYBBQUHAQEEUzBRME8GCCsGAQUFBzAChkNodHRwOi8v
aW5mcmEuc3dlZGJhbmsuY29tL3BraS9jcnQvU3dlZGJhbmtfRzNfSXNzdWluZ19D
QV9UaWVyX0EuY3J0MAsGA1UdDwQEAwIHgDA9BgkrBgEEAYI3FQcEMDAuBiYrBgEE
AYI3FQiD+8FigsjKGYf1jQeCwoAkgebsfGCCo+dNg/3yXAIBZAIBAzAVBgNVHSUE
DjAMBgorBgEEAYI3CgMMMB0GCSsGAQQBgjcVCgQQMA4wDAYKKwYBBAGCNwoDDDAN
BgkqhkiG9w0BAQUFAAOCAQEAco/ZCin6P5GqnAmkn/lKVLZvTnBeg9lsh0Y54sQr
KyXDJyBfJRjml1tGsFPSsi4kX0TjvQABo2o4v/JyRM5Kk1G+ytwOBzMk1oxEAvGj
/83oyPl8Ch483c3TJt9d5s9YKPtaPWgVgvhQpHWhoUwqO+AHhz2KWnoTVeewLa1Z
97ZVlp7J9gN9jmwjGEn/jOEaSPtrh4igF8OdqKILxTHeynN+30nDM9D5Z+KkGC3T
+ZWi5Ivtmzxwh9xNBMmBJDfU7aUG9FKpbJqUmMh6ddZFUjMW3DixALA2JIobiAy8
dkI0O60W+YGbfkkSv5ymwZjSI3k2XK75xitnEH8x/gO15w==
-----END CERTIFICATE-----';

    public function __construct()
    {
        $this->name = 'swedbank';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.5.13f';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Darius Augaitis';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Swedbank ecommerce');
        $this->description = $this->l('Swedbank ecommerce payment module with Swedbank bank link payment initiation');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function hookActionGetExtraMailTemplateVars(array &$params)
    {


        $template = $params['template'];
        if ('payment' != $template) {
            return;
        }

        $idOrder = $params['template_vars']['{id_order}'];
        $order = new Order($idOrder);

        if (!Validate::isLoadedObject($order) || $order->module != $this->name) {
            return;
        }

        $params['extra_template_vars']['{swedbank_html_block}'] = '';
        $params['extra_template_vars']['{swedbank_txt_block}'] = '';

        include_once __DIR__ . '/src/Entity/SwedbankCardPaymentData.php';

        $cardData = new PrestaShopCollection('SwedbankCardPaymentData');
        $cardData->where('id_order', '=', $order->id);
        $cardData = $cardData->getFirst();

        if (!$cardData instanceof SwedbankCardPaymentData) {
            return;
        }
        $tplVars = [
            'pan' => $cardData->pan,
            'expiry_date' => $cardData->expiry_date,
            'authorization_code' => $cardData->authorization_code,
            'merchant_reference' => $cardData->merchant_reference,
            'fullfil_date' => $cardData->fulfill_date,
        ];

        $this->context->smarty->assign($tplVars);

        $params['extra_template_vars']['{swedbank_html_block}'] = $this->context->smarty->fetch(
            $this->getLocalPath().'views/templates/hook/actionGetExtraMailTemplateVars.tpl'
        );
        $params['extra_template_vars']['{swedbank_txt_block}'] = $this->context->smarty->fetch(
            $this->getLocalPath().'views/templates/hook/actionGetExtraMailTemplateVars.txt'
        );
        //file_put_contents('mail.txt', print_r($params, true));
    }

    public function install()
    {
        $installSqlFiles = glob(__DIR__.'/sql/install/*.sql');

        if (!empty($installSqlFiles)) {

            foreach ($installSqlFiles as $sqlFile) {
                $sqlStatements = Tools::file_get_contents($sqlFile);
                $sqlStatements = str_replace('PREFIX_', _DB_PREFIX_, $sqlStatements);
                $sqlStatements = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sqlStatements);

                if (!Db::getInstance()->execute($sqlStatements)) {
                    file_put_contents('aaa.txt', 'Failed to execute SQL in file `%s`');
                    throw new Exception(sprintf('Failed to execute SQL in file `%s`', $sqlFile));
                }
            }

        }

        $this->addOrderState('Awaiting payment');
        $this->addOrderState('Payment requires investigation');

        if (!parent::install()  || !$this->registerHook('paymentOptions') || !$this->registerHook('actionGetExtraMailTemplateVars')) {
            return false;
        }
        return true;
    }

    public function addOrderState($name)
    {
        $state_exist = false;
        $states = OrderState::getOrderStates((int)$this->context->language->id);

        // check if order state exist
        foreach ($states as $state) {
            if (in_array($name, $state)) {
                $state_exist = true;
                break;
            }
        }

        // If the state does not exist, we create it.
        if (!$state_exist) {
            // create new order state
            $order_state = new OrderState();
            $order_state->color = '#00ffff';
            $order_state->send_email = false;
            $order_state->module_name = 'Swedbank';
            //$order_state->template = '';
            $order_state->name = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
                $order_state->name[ $language['id_lang'] ] = $name;

            // Update object
            $order_state->add();

        }

        return true;
    }

    public function hookPaymentOptions()
    {

        $payment_options = [];

        if (!$this->active || !$this->isConfigured()) {
            return [];
        }

        $cartCurrency = new Currency($this->context->cart->id_currency);
        if ('EUR' != $cartCurrency->iso_code) {
            return [];
        }

        //swedbank_testvtid_lt swedbank_testpass_lt
        //swedbank_vtid_lt swedbank_pass_lt


        if((!empty(Configuration::get('swedbank_testvtid_lt')) && !empty(Configuration::get('swedbank_testpass_lt')) && !Configuration::get('swedbank_testmode_lt')) ||
            (!empty(Configuration::get('swedbank_vtid_lt')) && !empty(Configuration::get('swedbank_pass_lt')) && Configuration::get('swedbank_testmode_lt')) ){

            $option = $this->getCardPaymentOption( 'lt');
            if($option){
                $payment_options[] = $option;
            }

            $option = $this->getSwedbankBanklinkOption('lt');
            if($option){
                $payment_options[] = $option;
            }
        }


        if((!empty(Configuration::get('swedbank_testvtid_lv')) && !empty(Configuration::get('swedbank_testpass_lv')) && !Configuration::get('swedbank_testmode_lv')) ||
            (!empty(Configuration::get('swedbank_vtid_lv')) && !empty(Configuration::get('swedbank_pass_lv')) && Configuration::get('swedbank_testmode_lv')) ){
            $option = $this->getCardPaymentOption( 'lv');
            if($option){
                $payment_options[] = $option;
            }

            $option = $this->getSwedbankBanklinkOption('lv');
            if($option){
                $payment_options[] = $option;
            }

        }

        if((!empty(Configuration::get('swedbank_testvtid_ee')) && !empty(Configuration::get('swedbank_testpass_ee')) && !Configuration::get('swedbank_testmode_ee')) ||
            (!empty(Configuration::get('swedbank_vtid_ee')) && !empty(Configuration::get('swedbank_pass_ee')) && Configuration::get('swedbank_testmode_ee')) ){

            $option = $this->getCardPaymentOption( 'ee');
            if($option){
                $payment_options[] = $option;
            }

            $option = $this->getSwedbankBanklinkOption('ee');
            if($option){
                $payment_options[] = $option;
            }

        }
        if(!empty(Configuration::get('swedbank_seller_id_lt')) && !empty(Configuration::get('swedbank_privatekey_lt')) && !empty(Configuration::get('swedbank_publickey_lt')) ) {
            $option = $this->getLTMBBLSwedbankOption('lt');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLSebOption('lt');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLCitadeleOption('lt');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLLuminorOption('lt');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLSiauliuOption('lt');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLMedicinosOption('lt');
            if ($option) {
                $payment_options[] = $option;
            }
        }

        if(!empty(Configuration::get('swedbank_seller_id_lv')) && !empty(Configuration::get('swedbank_privatekey_lv')) && !empty(Configuration::get('swedbank_publickey_lv')) ) {
            $option = $this->getLTMBBLSwedbankOption('lv');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLSebOption('lv');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLCitadeleOption('lv');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLLuminorOption('lv');
            if ($option) {
                $payment_options[] = $option;
            }
        }

        if(!empty(Configuration::get('swedbank_seller_id_ee')) && !empty(Configuration::get('swedbank_privatekey_ee')) && !empty(Configuration::get('swedbank_publickey_ee')) ) {
            $option = $this->getLTMBBLSwedbankOption('ee');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLSebOption('ee');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLCitadeleOption('ee');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLLhvOption('ee');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLPankOption('ee');
            if ($option) {
                $payment_options[] = $option;
            }

            $option = $this->getLTMBBLLuminorOption('ee');
            if ($option) {
                $payment_options[] = $option;
            }
        }

        return $payment_options;
    }

    private function getCardPaymentOption($lang = 'lt'){

        $show = Configuration::get('swedbank_'.$lang.'_card_status');

        if($show){
            $cardpayment = new PaymentOption();
            $cardpayment//->setCallToActionText($this->l('Card payment'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentcard', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/cards.png'));

            return $cardpayment;
        } else {
            return false;
        }

    }

    private function getSwedbankBanklinkOption($lang = 'lt'){

        $show = Configuration::get('swedbank_'.$lang.'_swedbank_status');

        if($show){
            $cardpayment = new PaymentOption();
            $cardpayment//->setCallToActionText($this->l('Swedbank'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentsw', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/Swedbank.png'));

            return $cardpayment;
        } else {
            return false;
        }

    }

    private function getLTMBBLSebOption($lang = 'lt'){

        $seb = Configuration::get('swedbank_'.$lang.'_seb_mbbl_status');
        //$citadele = Configuration::get('swedbank_lt_citadele_mbbl_status');

        if($seb){
            $cardpayment = new PaymentOption();
            $cardpayment//->setCallToActionText($this->l('SEB'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentseb', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/SEB_k_sw.png'));

            return $cardpayment;
        } else {
            return false;
        }

    }

    private function getLTMBBLSwedbankOption($lang = 'lt'){

        $swedbank = Configuration::get('swedbank_'.$lang.'_swedbank_mbbl_status');

        if($swedbank){
            $cardpayment = new PaymentOption();
            $cardpayment//->setCallToActionText($this->l('SEB'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentswedbank', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/Swedbank.png'));

            return $cardpayment;
        } else {
            return false;
        }

    }

    private function getLTMBBLCitadeleOption($lang = 'lt'){

        $citadele = Configuration::get('swedbank_'.$lang.'_citadele_mbbl_status');

        if($citadele){
            $cardpayment = new PaymentOption();
            $cardpayment//->setCallToActionText($this->l('Citadele'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentcitadele', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/citadele.png'));

            return $cardpayment;
        } else {
            return false;
        }

    }

    private function getLTMBBLLhvOption($lang = 'lt'){

        $lhv = Configuration::get('swedbank_'.$lang.'_lhv_mbbl_status');

        if($lhv){
            $cardpayment = new PaymentOption();
            $cardpayment//->setCallToActionText($this->l('LHV'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentlhv', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/lhvlogo.png'));

            return $cardpayment;
        } else {
            return false;
        }

    }

    private function getLTMBBLPankOption($lang = 'lt'){

        $lhv = Configuration::get('swedbank_'.$lang.'_pank_mbbl_status');

        if($lhv){
            $cardpayment = new PaymentOption();
            $cardpayment//->setCallToActionText($this->l('Pank'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentcoop', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/Coop.png'));

            return $cardpayment;
        } else {
            return false;
        }

    }

    private function getLTMBBLLuminorOption($lang = 'lt'){

        $luminor = Configuration::get('swedbank_'.$lang.'_luminor_mbbl_status');

        if($luminor){
            $banklink = new PaymentOption();
            $banklink//->setCallToActionText($this->l('Luminor'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentluminor', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/luminor.png'));

            return $banklink;
        } else {
            return false;
        }

    }

    private function getLTMBBLSiauliuOption($lang = 'lt'){

        $siauliu = Configuration::get('swedbank_'.$lang.'_siauliu_mbbl_status');

        if($siauliu){
            $banklink = new PaymentOption();
            $banklink//->setCallToActionText($this->l('Siauliu'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentsiauliu', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/siauiu_bankas.jpg'));

            return $banklink;
        } else {
            return false;
        }

    }

    private function getLTMBBLMedicinosOption($lang = 'lt'){

        $medicinos = Configuration::get('swedbank_'.$lang.'_medicinos_mbbl_status');

        if($medicinos){
            $banklink = new PaymentOption();
            $banklink//->setCallToActionText($this->l('Medicinos'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('swpaymentmedicinos', $lang, Configuration::get('swedbank_testmode_'.$lang) ? 1 : 0), true))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/templates/img/med_bankas.jpg'));

            return $banklink;
        } else {
            return false;
        }

    }
    //

    /**
     * Check if module is configured
     *
     * @return bool
     */
    public function isConfigured()
    {

        if(!Configuration::get('swedbank_status'))
            return false;

       /* $lt = false;
        $lv = false;
        $ee = false;

        if(Configuration::get('swedbank_lt_card_status')){
            $en = Configuration::get('swedbank_testmode_lt');
            if(!$en){
                $lt = (!empty(Configuration::get('swedbank_testvtid_lt')) && !empty(Configuration::get('swedbank_testpass_lt'))) ? true : false;
            } else {
                $lt = (!empty(Configuration::get('swedbank_vtid_lt')) && !empty(Configuration::get('swedbank_pass_lt'))) ? true : false;
            }
        }

        if(Configuration::get('swedbank_lv_card_status')){
            $en = Configuration::get('swedbank_testmode_lv');
            if(!$en){
                $lv = (!empty(Configuration::get('swedbank_testvtid_lv')) && !empty(Configuration::get('swedbank_testpass_lv'))) ? true : false;
            } else {
                $lv = (!empty(Configuration::get('swedbank_vtid_lv')) && !empty(Configuration::get('swedbank_pass_lv'))) ? true : false;
            }
        }

        if(Configuration::get('swedbank_ee_card_status')){
            $en = Configuration::get('swedbank_testmode_ee');
            if(!$en){
                $ee = (!empty(Configuration::get('swedbank_testvtid_ee')) && !empty(Configuration::get('swedbank_testpass_ee'))) ? true : false;
            } else {
                $ee = (!empty(Configuration::get('swedbank_vtid_ee')) && !empty(Configuration::get('swedbank_pass_ee'))) ? true : false;
            }
        }*/

        $euro = Currency::getIdByIsoCode('EUR', $this->context->shop->id);

        if (!$euro) {
            return false;
        } else return  true;

        //return $lt || $lv || $ee  ? true : false;
    }

    protected function generateForm()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = sprintf("%02d", $i);
        }

        $years = [];
        for ($i = 0; $i <= 10; $i++) {
            $years[] = date('Y', strtotime('+'.$i.' years'));
        }

        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'months' => $months,
            'years' => $years,
        ]);

        return $this->context->smarty->fetch($this->getLocalPath().'/views/templates/front/payment_form.tpl');
    }

    //------------------------------------------- ADMIN PART -------------------------------------------------------

    public function getContent()
    {

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        $this->context->smarty->assign([
            'notificationUrl' => $this->context->link->getModuleLink(
                $this->name,
                'notification',
                [
                    'secret_key' => Tools::encrypt($this->name)
                ]
            ),
            'cronTaskUrl' => $this->context->link->getModuleLink('swedbank', 'cronjob', array(), true).'?swedbankToken='.
                Tools::encrypt($this->name)
        ]);

        $this->_html .=$this->display(__FILE__, 'infos.tpl');
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function renderForm()
    {

        $allStates = OrderState::getOrderStates($this->context->language->id);
        $paidFlagStates = $this->getPaidFlagStates($allStates);
        $excludePaidStates = $this->getNonPaidFlagStates($allStates);

        $debugFile = $this->context->link->getModuleLink('swedbank', 'logfile', array(), true);
        $this->context->smarty->assign(
            'termsAndConditionsLink',
            $this->getPathUri().'pdf/terms_and_conditions.pdf'
        );

        $this->context->smarty->assign('debugFileUrl', $debugFile);

        $debugFileHtml = $this->context->smarty->fetch(
            $this->getLocalPath().'views/templates/front/log-file.tpl'
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('General', array(), 'Modules.Swedbank.Admin'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'label' =>
                            $this->context->smarty->fetch($this->getLocalPath().'/views/templates/front/terms-and-conditions-link.tpl'),
                        'name' => 'swedbank_status',
                        'type' => 'switch',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable debug mode', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_debuging',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        ),
                        'desc' => $debugFileHtml,
                    )
                    ,
                   array(
                        'label' => $this->l('Order status - successful payment', 'swedbank'),
                        'name' => 'SB_ORDER_STATUS_SUCCESS',
                        'type' => 'select',
                        'disabled' => false,
                        'options' => array(
                            'query' => $paidFlagStates,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ),
                    ),
                   array(
                        'label' => $this->l('Order status - awaiting payment', 'swedbank'),
                        'name' => 'SB_ORDER_STATUS_AWAITING',
                        'type' => 'select',
                        'disabled' => false,
                        'options' => array(
                            'query' => $excludePaidStates,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'label' => $this->l('Order status - requires investigation', 'swedbank'),
                        'name' => 'SB_ORDER_STATUS_REQINV',
                        'type' => 'select',
                        'disabled' => false,
                        'options' => array(
                            'query' => $excludePaidStates,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ),
                    )

                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $fields_form_lithuania = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('LITHUANIA - if signed with Swedbank Lithuania for SPP contract', array(), 'swedbank'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Enable/Disable Card payment', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_card_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Swedbank Lithuania banklink', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_swedbank_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),

                    array(
                        'label' => $this->l('Enable/Disable Production mode', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_testmode_lt',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Test environment ID (vTID)', array(), 'swedbank'),
                        'name' => 'swedbank_testvtid_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Test environment Password', array(), 'swedbank'),
                        'name' => 'swedbank_testpass_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Production environment ID (vTID)', array(), 'swedbank'),
                        'name' => 'swedbank_vtid_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Production environment Password', array(), 'swedbank'),
                        'name' => 'swedbank_pass_lt',
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $fields_form_lithuania_mbbl = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Lithuania - Signed contract with Swedbank Lithuania for stand alone Swedbank Bank Link and/or Payment Initiation', array(), 'swedbank'),
                    'icon' => 'icon-cogs'

                ),
                //'desc' => 'To generate certificate please fill fields: "State or province name", "Locality name", "Organization name", "Organizational unit name", "Common name", "Email address" and press Save. If you have your own certificate you only need to enter private key and Seller ID',
                'input' => array(
                    array(
                        'label' => $this->l('Enable/Disable Stand alone Swedbank banklink', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_swedbank_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable SEB Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_seb_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Citadele Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_citadele_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Luminor Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_luminor_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Siauliu Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_siauliu_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Medicinos Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lt_medicinos_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                   /* array(
                        'type' => 'text',
                        'label' => $this->trans('State or province name', array(), 'swedbank'),
                        'name' => 'swedbank_stateOrProvinceNamee_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Locality name', array(), 'swedbank'),
                        'name' => 'swedbank_localityName_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Organization name', array(), 'swedbank'),
                        'name' => 'swedbank_organizationName_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Organizational unit name', array(), 'swedbank'),
                        'name' => 'swedbank_organizationalUnitName_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Common name', array(), 'swedbank'),
                        'name' => 'swedbank_commonName_lt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Email address', array(), 'swedbank'),
                        'name' => 'swedbank_emailAddress_lt',
                    ),*/

                    array(
                        'type' => 'text',
                        'label' => $this->trans('Seller ID', array(), 'swedbank'),
                        'name' => 'swedbank_seller_id_lt',
                    ),

                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Private key', array(), 'swedbank'),
                        'name' => 'swedbank_privatekey_lt',
                    ),
                   /* array(
                        'type' => 'textarea',
                        'label' => $this->trans('Certificate (Send this to swedbank)', array(), 'swedbank'),
                        'name' => 'swedbank_certificate_lt',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Certificate request (Send this to Swedbank)', array(), 'swedbank'),
                        'name' => 'swedbank_cert_req_lt',
                    ),*/
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Swedbank public key', array(), 'swedbank'),
                        'name' => 'swedbank_publickey_lt',
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $fields_form_latvian_mbbl = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Latvia - Signed contract with Swedbank Latvia for stand alone Swedbank Bank Link and/or Payment Initiation', array(), 'swedbank'),
                    'icon' => 'icon-cogs'
                ),
                //'desc' => 'To generate certificate please fill fields: "State or province name", "Locality name", "Organization name", "Organizational unit name", "Common name", "Email address" and press Save. If you have your own certificate you only need to enter private key and Seller ID',
                'input' => array(
                    array(
                        'label' => $this->l('Enable/Disable Stand alone Swedbank banklink', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lv_swedbank_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable SEB Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lv_seb_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Citadele Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lv_citadele_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Luminor Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lv_luminor_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    /*array(
                        'type' => 'text',
                        'label' => $this->trans('State or province name', array(), 'swedbank'),
                        'name' => 'swedbank_stateOrProvinceNamee_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Locality name', array(), 'swedbank'),
                        'name' => 'swedbank_localityName_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Organization name', array(), 'swedbank'),
                        'name' => 'swedbank_organizationName_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Organizational unit name', array(), 'swedbank'),
                        'name' => 'swedbank_organizationalUnitName_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Common name', array(), 'swedbank'),
                        'name' => 'swedbank_commonName_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Email address', array(), 'swedbank'),
                        'name' => 'swedbank_emailAddress_lv',
                    ),*/
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Seller ID', array(), 'swedbank'),
                        'name' => 'swedbank_seller_id_lv',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Private key', array(), 'swedbank'),
                        'name' => 'swedbank_privatekey_lv',
                    ),
                    /*array(
                        'type' => 'textarea',
                        'label' => $this->trans('Certificate (Send this to swedbank)', array(), 'swedbank'),
                        'name' => 'swedbank_certificate_lv',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Certificate request (Send this to Swedbank)', array(), 'swedbank'),
                        'name' => 'swedbank_cert_req_lv',
                    ),*/
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Swedbank public key', array(), 'swedbank'),
                        'name' => 'swedbank_publickey_lv',
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $fields_form_estonia_mbbl = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Estonia - Signed contract with Swedbank Estonia for stand alone Swedbank Bank Link and/or Payment Initiation', array(), 'swedbank'),
                    'icon' => 'icon-cogs'
                ),
                //'desc' => 'To generate certificate please fill fields: "State or province name", "Locality name", "Organization name", "Organizational unit name", "Common name", "Email address" and press Save. If you have your own certificate you only need to enter private key and Seller ID',
                'input' => array(
                    array(
                        'label' => $this->l('Enable/Disable Stand alone Swedbank banklink', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_swedbank_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable SEB Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_seb_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Citadele Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_citadele_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable LHV Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_lhv_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable AS Coop Pank Eesti Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_pank_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Luminor Payment Initiation', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_luminor_mbbl_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),

                    /*array(
                        'type' => 'text',
                        'label' => $this->trans('State or province name', array(), 'swedbank'),
                        'name' => 'swedbank_stateOrProvinceNamee_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Locality name', array(), 'swedbank'),
                        'name' => 'swedbank_localityName_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Organization name', array(), 'swedbank'),
                        'name' => 'swedbank_organizationName_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Organizational unit name', array(), 'swedbank'),
                        'name' => 'swedbank_organizationalUnitName_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Common name', array(), 'swedbank'),
                        'name' => 'swedbank_commonName_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Email address', array(), 'swedbank'),
                        'name' => 'swedbank_emailAddress_ee',
                    ),*/

                    array(
                        'type' => 'text',
                        'label' => $this->trans('Seller ID', array(), 'swedbank'),
                        'name' => 'swedbank_seller_id_ee',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Private key', array(), 'swedbank'),
                        'name' => 'swedbank_privatekey_ee',
                    ),
                    /*array(
                        'type' => 'textarea',
                        'label' => $this->trans('Certificate (Send this to swedbank)', array(), 'swedbank'),
                        'name' => 'swedbank_certificate_ee',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Certificate request (Send this to Swedbank)', array(), 'swedbank'),
                        'name' => 'swedbank_cert_req_ee',
                    ),*/
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Swedbank public key', array(), 'swedbank'),
                        'name' => 'swedbank_publickey_ee',
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $seperator = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Bank Link Payment Initiation', array(), 'swedbank'),
                    'icon' => 'icon-cogs'
                ),
                'desc' => 'Bellow this point settings are for stand alone Swedbank Bank Link including Payment Initiation',
            ));

        $fields_form_latvia = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('LATVIA - if signed with Swedbank Latvia for SPP contract', array(), 'swedbank'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Enable/Disable Card payment', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lv_card_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Swedbank Latvia banklink', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_lv_swedbank_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),

                    array(
                        'label' => $this->l('Enable/Disable Production mode', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_testmode_lv',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Test environment ID (vTID)', array(), 'swedbank'),
                        'name' => 'swedbank_testvtid_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Test environment Password', array(), 'swedbank'),
                        'name' => 'swedbank_testpass_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Production environment ID (vTID)', array(), 'swedbank'),
                        'name' => 'swedbank_vtid_lv',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Production environment Password', array(), 'swedbank'),
                        'name' => 'swedbank_pass_lv',
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $fields_form_estonia = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('ESTONIA - if signed with Swedbank Estonia for SPP contract', array(), 'swedbank'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Enable/Disable Card payment', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_card_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Swedbank Estonia banklink', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_ee_swedbank_status',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'label' => $this->l('Enable/Disable Production mode', 'swedbank'),
                        'type' => 'switch',
                        'name' => 'swedbank_testmode_ee',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'activate_on',
                                'value' => true,
                                'label' => $this->l('Yes', 'swedbank')
                            ),
                            array(
                                'id' => 'activate_off',
                                'value' => false,
                                'label' => $this->l('No', 'swedbank')
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Test environment ID (vTID)', array(), 'swedbank'),
                        'name' => 'swedbank_testvtid_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Test environment Password', array(), 'swedbank'),
                        'name' => 'swedbank_testpass_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Production environment ID (vTID)', array(), 'swedbank'),
                        'name' => 'swedbank_vtid_ee',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Production environment Password', array(), 'swedbank'),
                        'name' => 'swedbank_pass_ee',
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
            .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form, $fields_form_lithuania, $fields_form_latvia, $fields_form_estonia, $seperator, $fields_form_lithuania_mbbl,
            $fields_form_latvian_mbbl, $fields_form_estonia_mbbl ));
    }

    private function getPaidFlagStates(array $allStates)
    {
        $result = [];
        foreach ($allStates as $state) {
            if ($state['paid']) {
                $result[] = $state;
            }
        }

        return $result;
    }

    private function getNonPaidFlagStates(array $allStates)
    {
        $result = [];
        foreach ($allStates as $state) {
            if (!$state['paid']) {
                $result[] = $state;
            }
        }

        return $result;
    }

    private function generateCert($country = 'LT'){

        //swedbank_stateOrProvinceNamee_ee swedbank_localityName_ee swedbank_organizationName_ee
        // swedbank_organizationalUnitName_ee swedbank_commonName_ee swedbank_emailAddress_ee
        $kal = strtolower($country);
        $dn = array(
            "countryName" => $country,
            "stateOrProvinceName" => Tools::getValue('swedbank_stateOrProvinceNamee_'.$kal, Configuration::get('swedbank_stateOrProvinceNamee_'.$kal)),
            "localityName" => Tools::getValue('swedbank_localityName_'.$kal, Configuration::get('swedbank_localityName_'.$kal)),
            "organizationName" => Tools::getValue('swedbank_organizationName_'.$kal, Configuration::get('swedbank_organizationName_'.$kal)),
            "organizationalUnitName" => Tools::getValue('swedbank_organizationalUnitName_'.$kal, Configuration::get('swedbank_organizationalUnitName_'.$kal)),
            "commonName" => Tools::getValue('swedbank_commonName_'.$kal, Configuration::get('swedbank_commonName_'.$kal)),
            "emailAddress" => Tools::getValue('swedbank_emailAddress_'.$kal, Configuration::get('swedbank_emailAddress_'.$kal))
        );

// Generate a new private (and public) key pair
        $privkey = openssl_pkey_new(array(
            "private_key_bits" => (int) 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));

// Generate a certificate signing request
        $csr = openssl_csr_new($dn, $privkey, array('digest_alg' => 'sha512'));

// Generate a self-signed cert, valid for 3 years
        $x509 = openssl_csr_sign($csr, null, $privkey, $days=1095, array('digest_alg' => 'sha512'));

// Save your private key, CSR and self-signed cert for later use
        openssl_csr_export($csr, $csrout);
        openssl_x509_export($x509, $certout);
        openssl_pkey_export($privkey, $pkeyout);

        return [$pkeyout, $certout, $csrout];
    }

    public function getConfigFieldsValues()
    {

        /*if(
        empty(Tools::getValue('swedbank_privatekey_lt', Configuration::get('swedbank_privatekey_lt'))) &&
        empty(Tools::getValue('swedbank_cert_req_lt', Configuration::get('swedbank_cert_req_lt'))) &&
        empty(Tools::getValue('swedbank_certificate_lt', Configuration::get('swedbank_certificate_lt'))) &&
        !empty(Tools::getValue('swedbank_stateOrProvinceNamee_lt', Configuration::get('swedbank_stateOrProvinceNamee_lt'))) &&
        !empty(Tools::getValue('swedbank_localityName_lt', Configuration::get('swedbank_localityName_lt'))) &&
        !empty(Tools::getValue('swedbank_organizationName_lt', Configuration::get('swedbank_organizationName_lt'))) &&
        !empty(Tools::getValue('swedbank_organizationalUnitName_lt', Configuration::get('swedbank_organizationalUnitName_lt'))) &&
        !empty(Tools::getValue('swedbank_commonName_lt', Configuration::get('swedbank_commonName_lt'))) &&
        !empty(Tools::getValue('swedbank_emailAddress_lt', Configuration::get('swedbank_emailAddress_lt')))
        ){
            $cert = $this->generateCert('LT');
            Configuration::updateValue('swedbank_privatekey_lt', $cert[0]);
            Configuration::updateValue('swedbank_certificate_lt', $cert[1]);
            Configuration::updateValue('swedbank_cert_req_lt', $cert[2]);
        }

        if(
            empty(Tools::getValue('swedbank_privatekey_lv', Configuration::get('swedbank_privatekey_lv'))) &&
            empty(Tools::getValue('swedbank_cert_req_lv', Configuration::get('swedbank_cert_req_lv'))) &&
            empty(Tools::getValue('swedbank_certificate_lv', Configuration::get('swedbank_certificate_lv'))) &&
            !empty(Tools::getValue('swedbank_stateOrProvinceNamee_lv', Configuration::get('swedbank_stateOrProvinceNamee_lv'))) &&
            !empty(Tools::getValue('swedbank_localityName_lv', Configuration::get('swedbank_localityName_lv'))) &&
            !empty(Tools::getValue('swedbank_organizationName_lv', Configuration::get('swedbank_organizationName_lv'))) &&
            !empty(Tools::getValue('swedbank_organizationalUnitName_lv', Configuration::get('swedbank_organizationalUnitName_lv'))) &&
            !empty(Tools::getValue('swedbank_commonName_lv', Configuration::get('swedbank_commonName_lv'))) &&
            !empty(Tools::getValue('swedbank_emailAddress_lv', Configuration::get('swedbank_emailAddress_lv')))
        ){
            $cert = $this->generateCert('LV');
            Configuration::updateValue('swedbank_privatekey_lv', $cert[0]);
            Configuration::updateValue('swedbank_certificate_lv', $cert[1]);
            Configuration::updateValue('swedbank_cert_req_lv', $cert[2]);
        }

        if(
            empty(Tools::getValue('swedbank_privatekey_ee', Configuration::get('swedbank_privatekey_ee'))) &&
            empty(Tools::getValue('swedbank_cert_req_ee', Configuration::get('swedbank_cert_req_ee'))) &&
            empty(Tools::getValue('swedbank_certificate_ee', Configuration::get('swedbank_certificate_ee'))) &&
            !empty(Tools::getValue('swedbank_stateOrProvinceNamee_ee', Configuration::get('swedbank_stateOrProvinceNamee_ee'))) &&
            !empty(Tools::getValue('swedbank_localityName_ee', Configuration::get('swedbank_localityName_ee'))) &&
            !empty(Tools::getValue('swedbank_organizationName_ee', Configuration::get('swedbank_organizationName_ee'))) &&
            !empty(Tools::getValue('swedbank_organizationalUnitName_ee', Configuration::get('swedbank_organizationalUnitName_ee'))) &&
            !empty(Tools::getValue('swedbank_commonName_ee', Configuration::get('swedbank_commonName_ee'))) &&
            !empty(Tools::getValue('swedbank_emailAddress_ee', Configuration::get('swedbank_emailAddress_ee')))
        ){
            $cert = $this->generateCert('EE');
            Configuration::updateValue('swedbank_privatekey_ee', $cert[0]);
            Configuration::updateValue('swedbank_certificate_ee', $cert[1]);
            Configuration::updateValue('swedbank_cert_req_ee', $cert[2]);
        }*/


        return array(

            'swedbank_status' => Tools::getValue('swedbank_status', Configuration::get('swedbank_status')),
            'SB_ORDER_STATUS_REQINV' => Tools::getValue('SB_ORDER_STATUS_REQINV', Configuration::get('SB_ORDER_STATUS_REQINV')),
            'SB_ORDER_STATUS_AWAITING' => Tools::getValue('SB_ORDER_STATUS_AWAITING', Configuration::get('SB_ORDER_STATUS_AWAITING')),
            'SB_ORDER_STATUS_SUCCESS' => Tools::getValue('SB_ORDER_STATUS_SUCCESS', Configuration::get('SB_ORDER_STATUS_SUCCESS')),
            'swedbank_debuging' => Tools::getValue('swedbank_debuging', Configuration::get('swedbank_debuging')),
            'swedbank_lt_card_status' => Tools::getValue('swedbank_lt_card_status', Configuration::get('swedbank_lt_card_status')),
            'swedbank_lt_swedbank_status' => Tools::getValue('swedbank_lt_swedbank_status', Configuration::get('swedbank_lt_swedbank_status')),
            'swedbank_lt_seb_status' => Tools::getValue('swedbank_lt_seb_status', Configuration::get('swedbank_lt_seb_status')),
            'swedbank_lt_dnb_status' => Tools::getValue('swedbank_lt_dnb_status', Configuration::get('swedbank_lt_dnb_status')),
            'swedbank_testmode_lt' => Tools::getValue('swedbank_testmode_lt', Configuration::get('swedbank_testmode_lt')),
            'swedbank_testvtid_lt' => Tools::getValue('swedbank_testvtid_lt', Configuration::get('swedbank_testvtid_lt')),
            'swedbank_testpass_lt' => Tools::getValue('swedbank_testpass_lt', Configuration::get('swedbank_testpass_lt')),
            'swedbank_vtid_lt' => Tools::getValue('swedbank_vtid_lt', Configuration::get('swedbank_vtid_lt')),
            'swedbank_pass_lt' => Tools::getValue('swedbank_pass_lt', Configuration::get('swedbank_pass_lt')),

            'swedbank_lv_card_status' => Tools::getValue('swedbank_lv_card_status', Configuration::get('swedbank_lv_card_status')),
            'swedbank_lv_swedbank_status' => Tools::getValue('swedbank_lv_swedbank_status', Configuration::get('swedbank_lv_swedbank_status')),
            'swedbank_lv_seb_status' => Tools::getValue('swedbank_lv_seb_status', Configuration::get('swedbank_lv_seb_status')),
            'swedbank_testmode_lv' => Tools::getValue('swedbank_testmode_lv', Configuration::get('swedbank_testmode_lv')),
            'swedbank_testvtid_lv' => Tools::getValue('swedbank_testvtid_lv', Configuration::get('swedbank_testvtid_lv')),
            'swedbank_testpass_lv' => Tools::getValue('swedbank_testpass_lv', Configuration::get('swedbank_testpass_lv')),
            'swedbank_vtid_lv' => Tools::getValue('swedbank_vtid_lv', Configuration::get('swedbank_vtid_lv')),
            'swedbank_pass_lv' => Tools::getValue('swedbank_pass_lv', Configuration::get('swedbank_pass_lv')),

            'swedbank_ee_card_status' => Tools::getValue('swedbank_ee_card_status', Configuration::get('swedbank_ee_card_status')),
            'swedbank_ee_swedbank_status' => Tools::getValue('swedbank_ee_swedbank_status', Configuration::get('swedbank_ee_swedbank_status')),
            'swedbank_testmode_ee' => Tools::getValue('swedbank_testmode_ee', Configuration::get('swedbank_testmode_ee')),
            'swedbank_testvtid_ee' => Tools::getValue('swedbank_testvtid_ee', Configuration::get('swedbank_testvtid_ee')),
            'swedbank_testpass_ee' => Tools::getValue('swedbank_testpass_ee', Configuration::get('swedbank_testpass_ee')),
            'swedbank_vtid_ee' => Tools::getValue('swedbank_vtid_ee', Configuration::get('swedbank_vtid_ee')),
            'swedbank_pass_ee' => Tools::getValue('swedbank_pass_ee', Configuration::get('swedbank_pass_ee')),

            'swedbank_lt_swedbank_mbbl_status' => Tools::getValue('swedbank_lt_swedbank_mbbl_status', Configuration::get('swedbank_lt_swedbank_mbbl_status')),
            'swedbank_lt_seb_mbbl_status' => Tools::getValue('swedbank_lt_seb_mbbl_status', Configuration::get('swedbank_lt_seb_mbbl_status')),
            'swedbank_lt_citadele_mbbl_status' => Tools::getValue('swedbank_lt_citadele_mbbl_status', Configuration::get('swedbank_lt_citadele_mbbl_status')),
            'swedbank_lt_luminor_mbbl_status' => Tools::getValue('swedbank_lt_luminor_mbbl_status', Configuration::get('swedbank_lt_luminor_mbbl_status')),
            'swedbank_lt_siauliu_mbbl_status' => Tools::getValue('swedbank_lt_siauliu_mbbl_status', Configuration::get('swedbank_lt_siauliu_mbbl_status')),
            'swedbank_lt_medicinos_mbbl_status' => Tools::getValue('swedbank_lt_medicinos_mbbl_status', Configuration::get('swedbank_lt_medicinos_mbbl_status')),

            'swedbank_seller_id_lt' => Tools::getValue('swedbank_seller_id_lt', Configuration::get('swedbank_seller_id_lt')),
            'swedbank_privatekey_lt' => Tools::getValue('swedbank_privatekey_lt', Configuration::get('swedbank_privatekey_lt')),
            'swedbank_publickey_lt' => Tools::getValue('swedbank_publickey_lt', empty(Configuration::get('swedbank_publickey_lt')) ? $this->sw_cer_lt : Configuration::get('swedbank_publickey_lt') ),
            //'swedbank_cert_req_lt' => Tools::getValue('swedbank_cert_req_lt', Configuration::get('swedbank_cert_req_lt')),
            //'swedbank_certificate_lt' => Tools::getValue('swedbank_certificate_lt', Configuration::get('swedbank_certificate_lt')),
            'swedbank_generate_but_lt' => Tools::getValue('swedbank_generate_but_lt', Configuration::get('swedbank_generate_but_lt')),
            //'swedbank_stateOrProvinceNamee_lt' => Tools::getValue('swedbank_stateOrProvinceNamee_lt', Configuration::get('swedbank_stateOrProvinceNamee_lt')),
            //'swedbank_localityName_lt' => Tools::getValue('swedbank_localityName_lt', Configuration::get('swedbank_localityName_lt')),
            //'swedbank_organizationName_lt' => Tools::getValue('swedbank_organizationName_lt', Configuration::get('swedbank_organizationName_lt')),
            //'swedbank_organizationalUnitName_lt' => Tools::getValue('swedbank_organizationalUnitName_lt', Configuration::get('swedbank_organizationalUnitName_lt')),
            //'swedbank_commonName_lt' => Tools::getValue('swedbank_commonName_lt', Configuration::get('swedbank_commonName_lt')),
            //'swedbank_emailAddress_lt' => Tools::getValue('swedbank_emailAddress_lt', Configuration::get('swedbank_emailAddress_lt')),

            'swedbank_lv_swedbank_mbbl_status' => Tools::getValue('swedbank_lv_swedbank_mbbl_status', Configuration::get('swedbank_lv_swedbank_mbbl_status')),
            'swedbank_lv_seb_mbbl_status' => Tools::getValue('swedbank_lv_seb_mbbl_status', Configuration::get('swedbank_lv_seb_mbbl_status')),
            'swedbank_lv_citadele_mbbl_status' => Tools::getValue('swedbank_lv_citadele_mbbl_status', Configuration::get('swedbank_lv_citadele_mbbl_status')),
            'swedbank_lv_luminor_mbbl_status' => Tools::getValue('swedbank_lv_luminor_mbbl_status', Configuration::get('swedbank_lv_luminor_mbbl_status')),

            'swedbank_seller_id_lv' => Tools::getValue('swedbank_seller_id_lv', Configuration::get('swedbank_seller_id_lv')),
            'swedbank_privatekey_lv' => Tools::getValue('swedbank_privatekey_lv', Configuration::get('swedbank_privatekey_lv')),
            'swedbank_publickey_lv' => Tools::getValue('swedbank_publickey_lv',empty(Configuration::get('swedbank_publickey_lv')) ? $this->sw_cer_lv : Configuration::get('swedbank_publickey_lv')),
            //'swedbank_cert_req_lv' => Tools::getValue('swedbank_cert_req_lv', Configuration::get('swedbank_cert_req_lv')),
            //'swedbank_certificate_lv' => Tools::getValue('swedbank_certificate_lv', Configuration::get('swedbank_certificate_lv')),
            'swedbank_generate_but_lv' => Tools::getValue('swedbank_generate_but_lv', Configuration::get('swedbank_generate_but_lv')),
            //'swedbank_stateOrProvinceNamee_lv' => Tools::getValue('swedbank_stateOrProvinceNamee_lv', Configuration::get('swedbank_stateOrProvinceNamee_lv')),
            //'swedbank_localityName_lv' => Tools::getValue('swedbank_localityName_lv', Configuration::get('swedbank_localityName_lv')),
            //'swedbank_organizationName_lv' => Tools::getValue('swedbank_organizationName_lv', Configuration::get('swedbank_organizationName_lv')),
            //'swedbank_organizationalUnitName_lv' => Tools::getValue('swedbank_organizationalUnitName_lv', Configuration::get('swedbank_organizationalUnitName_lv')),
            //'swedbank_commonName_lv' => Tools::getValue('swedbank_commonName_lv', Configuration::get('swedbank_commonName_lv')),
            //'swedbank_emailAddress_lv' => Tools::getValue('swedbank_emailAddress_lv', Configuration::get('swedbank_emailAddress_lv')),

            'swedbank_ee_swedbank_mbbl_status' => Tools::getValue('swedbank_ee_swedbank_mbbl_status', Configuration::get('swedbank_ee_swedbank_mbbl_status')),
            'swedbank_ee_seb_mbbl_status' => Tools::getValue('swedbank_ee_seb_mbbl_status', Configuration::get('swedbank_ee_seb_mbbl_status')),
            'swedbank_ee_citadele_mbbl_status' => Tools::getValue('swedbank_ee_citadele_mbbl_status', Configuration::get('swedbank_ee_citadele_mbbl_status')),
            'swedbank_ee_luminor_mbbl_status' => Tools::getValue('swedbank_ee_luminor_mbbl_status', Configuration::get('swedbank_ee_luminor_mbbl_status')),

            'swedbank_seller_id_ee' => Tools::getValue('swedbank_seller_id_ee', Configuration::get('swedbank_seller_id_ee')),
            'swedbank_privatekey_ee' => Tools::getValue('swedbank_privatekey_ee', Configuration::get('swedbank_privatekey_ee')),
            'swedbank_publickey_ee' => Tools::getValue('swedbank_publickey_ee', empty(Configuration::get('swedbank_publickey_ee')) ? $this->sw_cer_ee : Configuration::get('swedbank_publickey_ee')),
           // 'swedbank_cert_req_ee' => Tools::getValue('swedbank_cert_req_ee', Configuration::get('swedbank_cert_req_ee')),
            //'swedbank_certificate_ee' => Tools::getValue('swedbank_certificate_ee', Configuration::get('swedbank_certificate_eet')),
            'swedbank_generate_but_ee' => Tools::getValue('swedbank_generate_but_ee', Configuration::get('swedbank_generate_but_ee')),
            //'swedbank_stateOrProvinceNamee_ee' => Tools::getValue('swedbank_stateOrProvinceNamee_ee', Configuration::get('swedbank_stateOrProvinceNamee_ee')),
            //'swedbank_localityName_ee' => Tools::getValue('swedbank_localityName_ee', Configuration::get('swedbank_localityName_ee')),
            //'swedbank_organizationName_ee' => Tools::getValue('swedbank_organizationName_ee', Configuration::get('swedbank_organizationName_ee')),
            //'swedbank_organizationalUnitName_ee' => Tools::getValue('swedbank_organizationalUnitName_ee', Configuration::get('swedbank_organizationalUnitName_ee')),
            //'swedbank_commonName_ee' => Tools::getValue('swedbank_commonName_ee', Configuration::get('swedbank_commonName_ee')),
            //'swedbank_emailAddress_ee' => Tools::getValue('swedbank_emailAddress_ee', Configuration::get('swedbank_emailAddress_ee')),
            'swedbank_ee_lhv_mbbl_status' => Tools::getValue('swedbank_ee_lhv_mbbl_status', Configuration::get('swedbank_ee_lhv_mbbl_status')),
            'swedbank_ee_pank_mbbl_status' => Tools::getValue('swedbank_ee_pank_mbbl_status', Configuration::get('swedbank_ee_pank_mbbl_status'))

        );
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('swedbank_status',
                Tools::getValue('swedbank_status'));

            /*if (!Tools::getValue('BANK_WIRE_DETAILS')) {
                $this->_postErrors[] = $this->trans('Account details are required.', array(), 'Modules.Wirepayment.Admin');
            } elseif (!Tools::getValue('BANK_WIRE_OWNER')) {
                $this->_postErrors[] = $this->trans('Account owner is required.', array(), "Modules.Wirepayment.Admin");
            }*/
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {

            //Configuration::updateValue('swedbank_status', Tools::getValue('swedbank_status'));
            Configuration::updateValue('SB_ORDER_STATUS_REQINV', Tools::getValue('SB_ORDER_STATUS_REQINV'));
            Configuration::updateValue('SB_ORDER_STATUS_AWAITING', Tools::getValue('SB_ORDER_STATUS_AWAITING'));
            Configuration::updateValue('SB_ORDER_STATUS_SUCCESS', Tools::getValue('SB_ORDER_STATUS_SUCCESS'));
            Configuration::updateValue('swedbank_debuging', Tools::getValue('swedbank_debuging'));

            Configuration::updateValue('swedbank_lt_card_status', Tools::getValue('swedbank_lt_card_status'));
            Configuration::updateValue('swedbank_lt_swedbank_status', Tools::getValue('swedbank_lt_swedbank_status'));
            Configuration::updateValue('swedbank_lt_seb_status', Tools::getValue('swedbank_lt_seb_status'));
            Configuration::updateValue('swedbank_lt_dnb_status', Tools::getValue('swedbank_lt_dnb_status'));
            Configuration::updateValue('swedbank_testmode_lt', Tools::getValue('swedbank_testmode_lt'));
            Configuration::updateValue('swedbank_testvtid_lt', Tools::getValue('swedbank_testvtid_lt'));
            Configuration::updateValue('swedbank_testpass_lt', Tools::getValue('swedbank_testpass_lt'));
            Configuration::updateValue('swedbank_vtid_lt', Tools::getValue('swedbank_vtid_lt'));
            Configuration::updateValue('swedbank_pass_lt', Tools::getValue('swedbank_pass_lt'));

            Configuration::updateValue('swedbank_lv_card_status', Tools::getValue('swedbank_lv_card_status'));
            Configuration::updateValue('swedbank_lv_swedbank_status', Tools::getValue('swedbank_lv_swedbank_status'));
            Configuration::updateValue('swedbank_lv_seb_status', Tools::getValue('swedbank_lv_seb_status'));
            Configuration::updateValue('swedbank_testmode_lv', Tools::getValue('swedbank_testmode_lv'));
            Configuration::updateValue('swedbank_testvtid_lv', Tools::getValue('swedbank_testvtid_lv'));
            Configuration::updateValue('swedbank_testpass_lv', Tools::getValue('swedbank_testpass_lv'));
            Configuration::updateValue('swedbank_vtid_lv', Tools::getValue('swedbank_vtid_lv'));
            Configuration::updateValue('swedbank_pass_lv', Tools::getValue('swedbank_pass_lv'));

            Configuration::updateValue('swedbank_ee_card_status', Tools::getValue('swedbank_ee_card_status'));
            Configuration::updateValue('swedbank_ee_swedbank_status', Tools::getValue('swedbank_ee_swedbank_status'));
            Configuration::updateValue('swedbank_testmode_ee', Tools::getValue('swedbank_testmode_ee'));
            Configuration::updateValue('swedbank_testvtid_ee', Tools::getValue('swedbank_testvtid_ee'));
            Configuration::updateValue('swedbank_testpass_ee', Tools::getValue('swedbank_testpass_ee'));
            Configuration::updateValue('swedbank_vtid_ee', Tools::getValue('swedbank_vtid_ee'));
            Configuration::updateValue('swedbank_pass_ee', Tools::getValue('swedbank_pass_ee'));

            Configuration::updateValue('swedbank_lt_swedbank_mbbl_status', Tools::getValue('swedbank_lt_swedbank_mbbl_status'));
            Configuration::updateValue('swedbank_lt_seb_mbbl_status', Tools::getValue('swedbank_lt_seb_mbbl_status'));
            Configuration::updateValue('swedbank_lt_citadele_mbbl_status', Tools::getValue('swedbank_lt_citadele_mbbl_status'));
            Configuration::updateValue('swedbank_lt_luminor_mbbl_status', Tools::getValue('swedbank_lt_luminor_mbbl_status'));
            Configuration::updateValue('swedbank_lt_siauliu_mbbl_status', Tools::getValue('swedbank_lt_siauliu_mbbl_status'));
            Configuration::updateValue('swedbank_lt_medicinos_mbbl_status', Tools::getValue('swedbank_lt_medicinos_mbbl_status'));
            Configuration::updateValue('swedbank_seller_id_lt', Tools::getValue('swedbank_seller_id_lt'));
            Configuration::updateValue('swedbank_privatekey_lt', Tools::getValue('swedbank_privatekey_lt'));
            Configuration::updateValue('swedbank_publickey_lt', empty(Tools::getValue('swedbank_publickey_lt')) ? $this->sw_cer_lt : Tools::getValue('swedbank_publickey_lt') );
            //Configuration::updateValue('swedbank_cert_req_lt', Tools::getValue('swedbank_cert_req_lt'));
            //Configuration::updateValue('swedbank_certificate_lt', Tools::getValue('swedbank_certificate_lt'));
            Configuration::updateValue('swedbank_generate_but_lt', Tools::getValue('swedbank_generate_but_lt'));
            //Configuration::updateValue('swedbank_stateOrProvinceNamee_lt', Tools::getValue('swedbank_stateOrProvinceNamee_lt'));
            //Configuration::updateValue('swedbank_localityName_lt', Tools::getValue('swedbank_localityName_lt'));
            //Configuration::updateValue('swedbank_organizationName_lt', Tools::getValue('swedbank_organizationName_lt'));
            //Configuration::updateValue('swedbank_organizationalUnitName_lt', Tools::getValue('swedbank_organizationalUnitName_lt'));
            //Configuration::updateValue('swedbank_commonName_lt', Tools::getValue('swedbank_commonName_lt'));
            //Configuration::updateValue('swedbank_emailAddress_lt', Tools::getValue('swedbank_emailAddress_lt'));

            Configuration::updateValue('swedbank_lv_swedbank_mbbl_status', Tools::getValue('swedbank_lv_swedbank_mbbl_status'));
            Configuration::updateValue('swedbank_lv_seb_mbbl_status', Tools::getValue('swedbank_lv_seb_mbbl_status'));
            Configuration::updateValue('swedbank_lv_citadele_mbbl_status', Tools::getValue('swedbank_lv_citadele_mbbl_status'));
            Configuration::updateValue('swedbank_lv_luminor_mbbl_status', Tools::getValue('swedbank_lv_luminor_mbbl_status'));
            Configuration::updateValue('swedbank_seller_id_lv', Tools::getValue('swedbank_seller_id_lv'));
            Configuration::updateValue('swedbank_privatekey_lv', Tools::getValue('swedbank_privatekey_lv'));
            Configuration::updateValue('swedbank_publickey_lv', empty(Tools::getValue('swedbank_publickey_lv')) ? $this->sw_cer_lv : Tools::getValue('swedbank_publickey_lv') );
            //Configuration::updateValue('swedbank_cert_req_lv', Tools::getValue('swedbank_cert_req_lv'));
            //Configuration::updateValue('swedbank_certificate_lv', Tools::getValue('swedbank_certificate_lv'));
            Configuration::updateValue('swedbank_generate_but_lv', Tools::getValue('swedbank_generate_but_lv'));
            //Configuration::updateValue('swedbank_stateOrProvinceNamee_lv', Tools::getValue('swedbank_stateOrProvinceNamee_lv'));
            //Configuration::updateValue('swedbank_localityName_lv', Tools::getValue('swedbank_localityName_lv'));
            //Configuration::updateValue('swedbank_organizationName_lv', Tools::getValue('swedbank_organizationName_lv'));
            //Configuration::updateValue('swedbank_organizationalUnitName_lv', Tools::getValue('swedbank_organizationalUnitName_lv'));
            //Configuration::updateValue('swedbank_commonName_lv', Tools::getValue('swedbank_commonName_lv'));
            //Configuration::updateValue('swedbank_emailAddress_lv', Tools::getValue('swedbank_emailAddress_lv'));

            Configuration::updateValue('swedbank_ee_swedbank_mbbl_status', Tools::getValue('swedbank_ee_swedbank_mbbl_status'));
            Configuration::updateValue('swedbank_ee_seb_mbbl_status', Tools::getValue('swedbank_ee_seb_mbbl_status'));
            Configuration::updateValue('swedbank_ee_citadele_mbbl_status', Tools::getValue('swedbank_ee_citadele_mbbl_status'));
            Configuration::updateValue('swedbank_ee_luminor_mbbl_status', Tools::getValue('swedbank_ee_luminor_mbbl_status'));
            Configuration::updateValue('swedbank_seller_id_ee', Tools::getValue('swedbank_seller_id_ee'));
            Configuration::updateValue('swedbank_privatekey_ee', Tools::getValue('swedbank_privatekey_ee'));
            Configuration::updateValue('swedbank_publickey_ee', empty(Tools::getValue('swedbank_publickey_ee')) ? $this->sw_cer_ee : Tools::getValue('swedbank_publickey_ee'));
            //Configuration::updateValue('swedbank_cert_req_ee', Tools::getValue('swedbank_cert_req_ee'));
            //Configuration::updateValue('swedbank_certificate_ee', Tools::getValue('swedbank_certificate_ee'));
            Configuration::updateValue('swedbank_generate_but_ee', Tools::getValue('swedbank_generate_but_ee'));
            //Configuration::updateValue('swedbank_stateOrProvinceNamee_ee', Tools::getValue('swedbank_stateOrProvinceNamee_ee'));
            //Configuration::updateValue('swedbank_localityName_ee', Tools::getValue('swedbank_localityName_ee'));
            //Configuration::updateValue('swedbank_organizationName_ee', Tools::getValue('swedbank_organizationName_ee'));
            //Configuration::updateValue('swedbank_organizationalUnitName_ee', Tools::getValue('swedbank_organizationalUnitName_ee'));
            //Configuration::updateValue('swedbank_commonName_ee', Tools::getValue('swedbank_commonName_ee'));
            //Configuration::updateValue('swedbank_emailAddress_ee', Tools::getValue('swedbank_emailAddress_ee'));
            Configuration::updateValue('swedbank_ee_lhv_mbbl_status', Tools::getValue('swedbank_ee_lhv_mbbl_status'));
            Configuration::updateValue('swedbank_ee_pank_mbbl_status', Tools::getValue('swedbank_ee_pank_mbbl_status'));
//swedbank_ee_lhv_mbbl_status
        }
        $this->_html .= $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
    }

    //------------------------------------------- END ADMIN --------------------------------------------------------

    public function initLogTask(){

        $fileName = $this->getLocalPath().'var/logs/swedbank.log';
        $this->sendHttpHeaders($fileName);
        die;
    }

}
