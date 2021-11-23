<?php

class SwedbankCronjobModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */

    public $module;

    public function postProcess()
    {

        if(Tools::encrypt('swedbank') !== Tools::getValue('swedbankToken'))
            die('Forbidden!');

        include_once __DIR__ . '/../../src/Entity/SwedbankOrderStatus.php';

        $paymentStatus = new SwedbankOrderStatus();
        $rezo = $paymentStatus->retrieveUnfinishedQuery();

        $swedbank = Module::getInstanceByName('swedbank');
        $i = '';

        if(isset($rezo)){
            foreach ($rezo as $value){
                $config = new Configuration();
                $order = new Order(Order::getOrderByCartId( $value['id_order']));
                $ref = $order->reference;
                if ($value['pmmm'] === 'c') {
                    include_once __DIR__ . '/../../includes/hps.php';
                    $ob = new swedbank_v2_hps(null, $config, $this->context->link->getPageLink('order', true, NULL, "step=3"), null, $value['lnv'], $value['t'], $value['merchant_ref'], null, $this->context->language, $ref);
                    $name = $this->module->l('Card');
                } else {
                    include_once __DIR__ . '/../../includes/banklink.php';
                    $ob = new swedbank_v2_banklink(null, $config, $this->context->link->getPageLink('order', true, NULL, "step=3"), null, $value['lnv'], $value['t'], $value['merchant_ref'], null, $this->context->language, $value['pmmm'], $ref);
                    $name = $this->module->l('Banklink');
                }

                $rez = $ob->complyte();

                $cart = new Cart($value['id_order']);

                $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
                //$currency = new Currency($cart->id_currency);
                $customer = new Customer((int)$cart->id_customer);

                $idOrderStatus = null;
                $mailV = [];

                //include_once __DIR__ . '/../../src/Entity/SwedbankOrderStatus.php';

                //$paymentStatus = new SwedbankOrderStatus();
                $statusPayment = $paymentStatus->retrieveStatus($value['merchant_ref']);

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

                        try {
                            $paymentCartData->save();
                        } catch (Exception $exception) {
                        }
                    } else {
                        $mailV = [];
                    }

                } elseif (2066 === (int)$rez[0] && (int)$statusPayment === 0) {
                    $idOrderStatus = Configuration::get('SB_ORDER_STATUS_REQINV');
                }

                if ($idOrderStatus) {
                    //include_once __DIR__ . '/../../src/Entity/SwedbankOrderStatus.php';

                    //$paymentStatus = new SwedbankOrderStatus();
                    $paymentStatus->updateItem($value['merchant_ref'], 1);
                    try{

                        $order_id = Order::getOrderByCartId((int)$cart->id);
                        $history = new OrderHistory();
                        $history->id_order = (int)$order_id;
                        $history->changeIdOrderState((int)$idOrderStatus, (int)$order_id, true);
                        $history->addWithemail();
                        $history->save();

                        //$swedbank->validateOrder($cart->id, (int)$idOrderStatus, $total, $name, NULL, $mailV, (int)$cart->id_currency, false, $customer->secure_key);
                    }
                    catch (Exception $e) {
                        include_once __DIR__ . '/../../includes/logger.php';
                        $log = new \Swedbank_Client_Logger();
                        $log->logData('Failed to set status for payment. merchant reference: '.$value['merchant_ref']);
                    }
                }
            }
        }
        die;
    }


}
