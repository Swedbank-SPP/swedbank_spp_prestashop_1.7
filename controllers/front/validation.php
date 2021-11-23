<?php

class SwedbankValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */

    public $module;

    public function postProcess()
    {

        $swedbank = Module::getInstanceByName('swedbank');

        if (Tools::getValue('swedbankv2') === 'doneB') {
            $config = new Configuration();
            include_once __DIR__. '/../../includes/logger.php';
            $log = new Swedbank_Client_Logger();

            try{

                require __DIR__ . '/../../includes/mbbl/Protocol/Protocol.php';

                $lng = $_GET['lnv'];

                $protocol = new Protocol(
                    trim($config->get('swedbank_seller_id_' . $lng)), // seller ID (VK_SND_ID)
                    trim($config->get('swedbank_privatekey_' . $lng)), // private key
                    '', // private key password, leave empty, if not neede
                    trim($config->get('swedbank_publickey_' . $lng)), // public key
                    '' // return url
                );

                require __DIR__ . '/../../includes/mbbl/Banklink.php';
                $banklink = new Banklink($protocol);

                $config->get('swedbank_debuging') ? $log->logData('POST: '.print_r($_POST, true)) : null;
                $config->get('swedbank_debuging') ? $log->logData('GET: '.print_r($_GET, true)) : null;

                $r = $banklink->handleResponse(empty($_POST) ? $_GET : $_POST);

                /*if ($r->wasSuccessful()) {
                    // Get whole array of response
                    $responseData    = $r->getResponseData();
                } else {
                    $this->errors = $this->module->l('Payment option failed. Please try later or choose another payment option.');
                    $this->redirectWithNotifications('index.php?controller=order&step=3');
                }*/

                $cart = new Cart(Tools::getValue('order_id'));

                $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
                //$currency = new Currency($cart->id_currency);


                $idOrderStatus = null;
                $mailV = [];

                include_once __DIR__ . '/../../src/Entity/SwedbankOrderStatus.php';

                $paymentStatus = new SwedbankOrderStatus();
                $statusPayment = $paymentStatus->retrieveStatus(Tools::getValue('order_id'));

                if ($r->wasSuccessful()) {
                    if ((int)$statusPayment === 0) {
                        include_once __DIR__ . '/../../src/Entity/SwedbankOrderStatus.php';

                        $paymentStatus = new SwedbankOrderStatus();
                        $paymentStatus->updateItem(Tools::getValue('order_id'), 1);


                        $order_id = Order::getOrderByCartId((int)$cart->id);
                        $history = new OrderHistory();
                        $history->id_order = (int)$order_id;

                        if($history->id_order_state != (int)Configuration::get('SB_ORDER_STATUS_SUCCESS')){
                            $history->changeIdOrderState((int)Configuration::get('SB_ORDER_STATUS_SUCCESS'), (int)$order_id, true);

                        }
                        //$history->sendEmail((int)$order_id);
                        try {
                            $history->addWithemail();
                        } catch (Exception $ex){

                        }

                        $history->save();

                        //$swedbank->validateOrder($cart->id, (int)$idOrderStatus, $total, $name, NULL, $mailV, (int)$cart->id_currency, false, $customer->secure_key);
                    }
                    $customer = new Customer((int)$cart->id_customer);
                    Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $swedbank->id . '&id_order=' . $swedbank->currentOrder . '&key=' . $customer->secure_key);
                } else {
                    $this->errors = $this->module->l('Payment option failed. Please try later or choose another payment option.');
                    $this->redirectWithNotifications('index.php?controller=order&step=3');
                }
            } catch (Exception $exception){
                $config->get('swedbank_debuging') ? $log->logData('Exception: '.print_r($exception, true)) : null;
                $this->errors = $this->module->l('Something went wrong. Please contact merchant to confirm or dismiss your order.');
                $this->redirectWithNotifications('index.php?controller=order&step=3');


            }



        } else if (Tools::getValue('swedbankv2') === 'done') {
            //die('-----------');
            $config = new Configuration();
            if (Tools::getValue('pmmm') === 'c') {
                include __DIR__ . '/../../includes/hps.php';
                $ob = new swedbank_v2_hps(null, $config, $this->context->link->getPageLink('order', true, NULL, "step=3"), null, Tools::getValue('lnv'), Tools::getValue('t'), Tools::getValue('order_id'), null, $this->context->language, Tools::getValue('re'));

            } else {
                include __DIR__ . '/../../includes/banklink.php';
                $ob = new swedbank_v2_banklink(null, $config, $this->context->link->getPageLink('order', true, NULL, "step=3"), null, Tools::getValue('lnv'), Tools::getValue('t'), Tools::getValue('order_id'), null, $this->context->language, Tools::getValue('pmmm'), Tools::getValue('re'));

            }

            $rez = $ob->complyte();

            $cart = new Cart(Tools::getValue('order_id'));

            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
            //$currency = new Currency($cart->id_currency);


            $idOrderStatus = null;
            $mailV = [];

            include_once __DIR__ . '/../../src/Entity/SwedbankOrderStatus.php';

            $paymentStatus = new SwedbankOrderStatus();
            $statusPayment = $paymentStatus->retrieveStatus(Tools::getValue('order_id'));

            if (1 === (int)$rez[0] && (int)$statusPayment === 0) {

                $idOrderStatus = Configuration::get('SB_ORDER_STATUS_SUCCESS');

                if (isset($rez[1])) {
                    include_once __DIR__ . '/../../src/Entity/SwedbankCardPaymentData.php';

                    $paymentCartData = new SwedbankCardPaymentData();
                    $paymentCartData->id_order = $cart->id;
                    $paymentCartData->expiry_date = $rez[1]['ex'];
                    $paymentCartData->pan = $rez[1]['pan'];
                    $paymentCartData->authorization_code = $rez[1]['authcode'];
                    $paymentCartData->merchant_reference = $rez[1]['merchant_reference'];
                    $paymentCartData->fulfill_date = $rez[1]['fulfill_date'];

                    /*$tplVars = [
                        'pan' => $rez[1]['pan'],
                        'expiry_date' => $rez[1]['ex'],
                        'authorization_code' => $rez[1]['authcode'],
                        'merchant_reference' => $rez[1]['merchant_reference'],
                        'fullfil_date' => $rez[1]['fulfill_date']
                    ];*/

                    //$this->context->smarty->assign($tplVars);

                    /*$mailV = [
                        '{swedbank_html_block}' => $this->context->smarty->fetch(__DIR__ . '/../../views/templates/hook/actionGetExtraMailTemplateVars.tpl'),
                        '{swedbank_txt_block}' => $this->context->smarty->fetch(__DIR__ . '/../../views/templates/hook/actionGetExtraMailTemplateVars.txt')
                    ];*/
                    try {
                        $paymentCartData->save();
                    } catch (Exception $exception) {
                    }
                } else {
                    $mailV = [];
                }

            } elseif (2066 === (int)$rez[0] && (int)$statusPayment === 0) {
                $idOrderStatus = Configuration::get('SB_ORDER_STATUS_REQINV');

                $history = new OrderHistory();
                $history->id_order = (int)$cart->id;
                $history->changeIdOrderState((int)$idOrderStatus, (int)($cart->id));
                $history->save();

                $this->errors = $this->module->l('Requires manual payment confirmation. Please contact shop owner, to confirm payment.');
                $this->redirectWithNotifications('index.php?controller=order&step=1');

            } elseif (2051 === (int)$rez[0] && (int)$statusPayment === 0) {
                $this->errors = $this->module->l('Payment pending. Please check after some time for status update.');
                $this->redirectWithNotifications('index.php');
            }

            if ($idOrderStatus || (int)$statusPayment === 1) {
                if ((int)$statusPayment === 0) {
                    include_once __DIR__ . '/../../src/Entity/SwedbankOrderStatus.php';

                    $paymentStatus = new SwedbankOrderStatus();
                    $paymentStatus->updateItem(Tools::getValue('order_id'), 1);


                    $order_id = Order::getOrderByCartId((int)$cart->id);
                    $history = new OrderHistory();
                    $history->id_order = (int)$order_id;

                    $history->changeIdOrderState((int)Configuration::get('SB_ORDER_STATUS_SUCCESS'), (int)$order_id, true);
                    //$history->sendEmail((int)$order_id);
                    try {
                        $history->addWithemail();
                    } catch (Exception $ex){

                    }
                    $history->save();

                    //$swedbank->validateOrder($cart->id, (int)$idOrderStatus, $total, $name, NULL, $mailV, (int)$cart->id_currency, false, $customer->secure_key);
                }
                $customer = new Customer((int)$cart->id_customer);
                Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $swedbank->id . '&id_order=' . $swedbank->currentOrder . '&key=' . $customer->secure_key);
            } else {
                $this->errors = $this->module->l('Payment option failed. Please try later or choose another payment option.');
                $this->redirectWithNotifications('index.php?controller=order&step=3');
            }


        } else {
            $bankType = '';

            $cart = $this->context->cart;
            if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
                $this->errors = $this->module->l('Payment Error: please try later.');
                $this->redirectWithNotifications('index.php?controller=order&step=1');
            }

            // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
            $authorized = false;
            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'swedbank') {
                    $authorized = true;
                    break;
                }
            }

            if (!$authorized) {
                $this->errors = $this->module->l('This payment method is not available.', 'validation');
                $this->redirectWithNotifications('index.php?controller=order&step=3');
            }

            $address = new Address(intval($this->context->cart->id_address_delivery));
            $address->email = $this->context->customer->email;
            $address->ip = Tools::getRemoteAddr();

            $countries = Country::getCountries($this->context->language->id, true);
            $address->countries = $countries;

            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

            $config = new Configuration();

            if ($_REQUEST[0] === 'swpaymentcard') {
                $name = $this->module->l('Card');
            } else if ($_REQUEST[0] === 'swpaymentseb') {
                $bankType = 'seb';
                $name = $this->module->l('SEB Banklink');
            } else if ($_REQUEST[0] === 'swpaymentcitadele') {
                $bankType = 'citadele';
                $name = $this->module->l('Citadele Banklink');
            } else if ($_REQUEST[0] === 'swpaymentlhv') {
                $bankType = 'lhv';
                $name = $this->module->l('LHV Banklink');
            } else if ($_REQUEST[0] === 'swpaymentcoop') {
                $bankType = 'coop';
                $name = $this->module->l('Coop Banklink');
            } else if ($_REQUEST[0] === 'swpaymentluminor') {
                $bankType = 'luminor';
                $name = $this->module->l('Luminor Banklink');
            } else if ($_REQUEST[0] === 'swpaymentsiauliu') {
                $bankType = 'siauliu';
                $name = $this->module->l('Siauliu Banklink');
            }  else if ($_REQUEST[0] === 'swpaymentmedicinos') {
                $bankType = 'medicinos';
                $name = $this->module->l('Medicinos Banklink');
            }   else {
                $bankType = 'swedbank';
                $name = $this->module->l('Swedbank Banklink');
            }

            $cart->save();

            $idOrderStatus = Configuration::get('SB_ORDER_STATUS_AWAITING');
            $customer = new Customer((int)$cart->id_customer);
            $swedbank->validateOrder($cart->id, (int)$idOrderStatus, $total, $name, NULL, array(), (int)$cart->id_currency, false, $customer->secure_key);

            // $order_id = Order::getOrderByCartId($cart->id);
            $order = new Order(Order::getOrderByCartId($cart->id));
            $ref = $order->reference;

            if ($_REQUEST[0] === 'swpaymentcard' || $_REQUEST[0] === 'swpaymentsw') {
                if ($_REQUEST[0] === 'swpaymentcard') {
                    include __DIR__ . '/../../includes/hps.php';
                    $ob = new swedbank_v2_hps($address, $config, $this->context->link->getPageLink('order', true, NULL, "step=3"), $total, $_REQUEST[1], $_REQUEST[2], $this->context->cart->id, $this->context->link->getModuleLink('swedbank', 'validation', array(), true), $this->context->language, $ref);
                } else {
                    include __DIR__ . '/../../includes/banklink.php';
                    $ob = new swedbank_v2_banklink($address, $config, $this->context->link->getPageLink('order', true, NULL, "step=3"), (int)($total * 100), $_REQUEST[1], $_REQUEST[2], $this->context->cart->id, $this->context->link->getModuleLink('swedbank', 'validation', array(), true), $this->context->language, $_REQUEST[0], $ref);
                }

                $respond = $ob->setupCon();


                if (!$respond) {
                    $this->errors = $this->module->l('Payment option failed. Please try later or choose another payment option.');
                    $this->redirectWithNotifications('index.php?controller=order&step=3');
                } else {
                    Tools::redirect($respond);
                }
            } else {
                require __DIR__ . '/../../includes/mbbl/Protocol/Protocol.php';

                $lng = $_REQUEST[1];

                $protocol = new Protocol(
                    trim($config->get('swedbank_seller_id_' . $lng)), // seller ID (VK_SND_ID)
                    $config->get('swedbank_privatekey_' . $lng), // private key
                    '', // private key password, leave empty, if not neede
                    $config->get('swedbank_publickey_' . $lng), // public key
                    $this->context->link->getModuleLink('swedbank', 'validation', array(), true) . '?swedbankv2=doneB&order_id=' . $this->context->cart->id . '&pmmm=c&t=' . $_REQUEST[2] . '&lnv=' . $_REQUEST[1] . '&re=' . $ref
                );

                require __DIR__ . '/../../includes/mbbl/Banklink.php';
                if ($lng == 'ee')
                    $lng = 'et';

                $banklink = new Banklink($protocol, $lng, $bankType);

                switch (strtolower($this->context->language->iso_code)) {
                    case 'en':
                        $lnv = 'ENG';
                        break;
                    case 'lt':
                        $lnv = 'LIT';
                        break;
                    case 'ee':
                        $lnv = 'EST';
                        break;
                    case 'et':
                        $lnv = 'EST';
                        break;
                    case 'ru':
                        $lnv = 'RUS';
                        break;
                    default:
                        $lnv = 'ENG';
                }
                //if($lng == 'et'){
                    $ordM = 'Order Nr: ' . $ref;
                    $ref = $ref;
                /*} else{
                    $ordM = 'Order Nr: ' . $ref;
                }*/

                $request = $banklink->getPaymentRequest($ref, $total, $ordM, $lnv);

                include_once __DIR__. '/../../includes/logger.php';
                $log = new Swedbank_Client_Logger();
                $config->get('swedbank_debuging') ? $log->logData(print_r($request->getRequestData(), true)) : null;

//echo $request->getRequestUrl();
                echo '
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <script type="text/javascript">
        function closethisasap() {
            document.forms["redirectpost"].submit();
        }
    </script>
<body onload="closethisasap();">
<form method="POST" name="redirectpost" action="' . $request->getRequestUrl() . '">

    ' . $request->getRequestInputs() . '
    <input type="submit" style="display: none;" value="Pay" />
</form>
</body>
</html>';
                die;


            }

        }
    }


}
