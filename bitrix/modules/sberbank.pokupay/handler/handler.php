<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Order;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

require_once dirname(dirname(__FILE__)) . '/config.php';
Loader::includeModule('sberbank.pokupay');

/**
 * Class SberbankPaymentHandler
 * @package Sale\Handlers\PaySystem
 */
class sberbank_pokupayHandler extends PaySystem\ServiceHandler implements PaySystem\IRefundExtended, PaySystem\IHold
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{

		$moduleId = 'sberbank.pokupay';
		
		$SBERBANK_Gateway = new \Sberbank\Credit\Gateway;
		$SBERBANK_Orders = new \Sberbank\Credit\Orders;

		
		// module settings
		$SBERBANK_Gateway->setOptions([
			'module_id' => Option::get($moduleId, 'MODULE_ID'),
			'gate_url_prod' => Option::get($moduleId, 'SBERBANK_PROD_URL'),
			'gate_url_test' => Option::get($moduleId, 'SBERBANK_TEST_URL'),
			'module_version' => Option::get($moduleId, 'MODULE_VERSION'),
			'iso' => unserialize(Option::get($moduleId, 'ISO')),
			'cms_version' => 'Bitrix ' . SM_VERSION,
			'language' => 'ru',
			'creditType' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_CREDIT_TYPE') == 'CREDIT' ? 'CREDIT' : 'INSTALLMENT'
		]);

		// handler settings
		$SBERBANK_Gateway->setOptions([

			'ofd_enabled' => 1,
			'ffd_version' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_FFD_VERSION'),
			'ffd_payment_object' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_FFD_PAYMENT_OBJECT'),
			'ffd_payment_method' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_FFD_PAYMENT_METHOD'),
			'test_mode' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_TEST_MODE') == 'Y' ? 1 : 0,
			'handler_logging' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_HANDLER_LOGGING') == 'Y' ? 1 : 0,
		    'failUrl' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_FAIL_URL'),
		]);

		$SBERBANK_Gateway->buildData([
			'orderNumber' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_NUMBER') . '_' . $payment->getField('ID'),
		    'amount' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_AMOUNT'),
		    'userName' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_LOGIN'),
		    'password' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_PASSWORD'),
		    'currency' => $SBERBANK_Gateway->getCurrencyCode( $payment->getField('CURRENCY') ),
		    'description' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_DESCRIPTION'),
		]);

		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off" ? 'https://' : 'http://';
		$domain_name = $_SERVER['HTTP_HOST'];

		if(SITE_DIR == '/' || strlen(SITE_DIR) == 0) {
			$site_dir = '/';
		} else {
			if(substr(SITE_DIR, 0, 1) != '/') {
			    $site_dir = '/' . SITE_DIR;
			}
			if(substr(SITE_DIR, -1, 1) != '/') {
			    $site_dir = SITE_DIR . '/';
			}
		}
		$returnUrlparams = '?PAYMENT=SBERBANK_POKUPAY&ORDER_ID=' . $payment->getField('ORDER_ID') . '&PAYMENT_ID=' . $payment->getField('ID') . '&USER_RETURN=1';
		

		if(strlen($this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_RETURN_URL')) > 0) {
			$SBERBANK_Gateway->buildData(['returnUrl' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_RETURN_URL') . $returnUrlparams]);
		} else {
			$SBERBANK_Gateway->buildData(['returnUrl' => $protocol . $domain_name . $site_dir . 'sberbank/credit_result.php' . $returnUrlparams]);
		}
		if(strlen($this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_FAIL_URL')) > 0) {
			$SBERBANK_Gateway->buildData(['failUrl' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_FAIL_URL') . $returnUrlparams]);
		} else {
			$SBERBANK_Gateway->buildData(['failUrl' => $protocol . $domain_name . $site_dir . 'sberbank/credit_result.php' . $returnUrlparams]);
		}

		$Order = Order::load($payment->getOrderId());
		$orderProperties = $Order->getPropertyCollection();

		if ($SBERBANK_Gateway->ofdEnable()) {
			$SBERBANK_Gateway->buildData([
				'description' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_DESCRIPTION')
			]);
			$Basket = $Order->getBasket();
			$basketItems = $Basket->getBasketItems();


			$phone_key = strlen(Option::get($moduleId, 'OPTION_PHONE')) > 0 ? Option::get($moduleId, 'OPTION_PHONE') : 'PHONE';
			$email_key = strlen(Option::get($moduleId, 'OPTION_EMAIL')) > 0 ? Option::get($moduleId, 'OPTION_EMAIL') : 'EMAIL';
			
			$phone = (int) preg_replace('/\D+/', '', $this->getPropertyValueByCode($orderProperties, $phone_key));

			$SBERBANK_Gateway->setOptions([
				'customer_name' => $this->getPropertyValueByCode($orderProperties, 'FIO'),
				'customer_email' => $this->getPropertyValueByCode($orderProperties, $email_key),
				'customer_phone' => $phone,
			]);

			$lastIndex = 0;
			foreach ($basketItems as $key => $BasketItem) {
				$lastIndex = $key + 1;
		        $SBERBANK_Gateway->setPosition([
		            'positionId' => $lastIndex,
		            'itemCode' => $BasketItem->getProductId(),
		            'name' => $BasketItem->getField('NAME'),
		            'itemAmount' => $BasketItem->getFinalPrice(),
		            'itemPrice' => $BasketItem->getPrice(),
		            'quantity' => array(
		                'value' => $BasketItem->getQuantity(),
		                'measure' => $BasketItem->getField('MEASURE_NAME') ? $BasketItem->getField('MEASURE_NAME')  : Loc::getMessage('SBERBANK_POKUPAY_FIELD_MEASURE'),
		            ),
		            'tax' => array(
		                'taxType' =>  $SBERBANK_Gateway->getTaxCode( $BasketItem->getField('VAT_RATE') * 100 ),
		            ),
		        ]);
			}

			if($Order->getField('PRICE_DELIVERY') > 0) {
				
				Loader::includeModule('catalog');
				$deliveryInfo = \Bitrix\Sale\Delivery\Services\Manager::getById($Order->getField('DELIVERY_ID'));

				$deliveryVatItem = \CCatalogVat::GetByID($deliveryInfo['VAT_ID'])->Fetch();
				$SBERBANK_Gateway->setOptions([
				    'delivery' => true,
				]);
				$SBERBANK_Gateway->setPosition([
		            'positionId' => $lastIndex + 1,
		            'itemCode' => 'DELIVERY_' . $Order->getField('DELIVERY_ID'),
		            'name' => Loc::getMessage('SBERBANK_POKUPAY_FIRLD_DELIVERY'),
		            'itemAmount' => $Order->getField('PRICE_DELIVERY'),
		            'itemPrice' => $Order->getField('PRICE_DELIVERY'),
		            'quantity' => array(
		                'value' => 1,
		                'measure' => Loc::getMessage('SBERBANK_POKUPAY_FIELD_MEASURE'),
		            ),
		            'tax' => array(
		                'taxType' => $SBERBANK_Gateway->getTaxCode($deliveryVatItem['RATE']),
		            ),
		        ]);	
			}
		}

		$gateResponse = $SBERBANK_Gateway->registerOrder();

		$params = array(
	        'sberbank_result' => $gateResponse,
	        'payment_link' => $SBERBANK_Gateway->getPaymentLink(),
	        'currency' => $payment->getField('CURRENCY'),
	    );
	    $this->setExtraParams($params);

	    $db_result = $SBERBANK_Orders->GetByPaymentID($payment->getField('ID'))->Fetch();
	    if(!$db_result) {
	    	$SBERBANK_Orders->Add([
	    		'USER_INFO' => $this->getPropertyValueByCode($orderProperties, 'FIO'),
	    		'PAYMENT_SUM' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_AMOUNT'),
	    		'CMS_ORDER_ID' => $payment->getOrderId(),
	    		'CMS_PAYMENT_ID' => $payment->getField('ID'),
		    ]);	
	    }
	    
	    return $this->showTemplate($payment, "payment");
	}

	public function processRequest(Payment $payment, Request $request)
	{
		global $APPLICATION;
		$moduleId = 'sberbank.pokupay';


		$SBERBANK_Gateway = new \Sberbank\Credit\Gateway;
		$SBERBANK_Orders = new \Sberbank\Credit\Orders;

		$SBERBANK_Gateway->setOptions([
			'gate_url_prod' => Option::get($moduleId, 'SBERBANK_PROD_URL'),
			'gate_url_test' => Option::get($moduleId, 'SBERBANK_TEST_URL'),
			'test_mode' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_TEST_MODE') == 'Y' ? 1 : 0,
		]);

		$SBERBANK_Gateway->buildData([
		    'userName' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_LOGIN'),
		    'password' => $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_API_PASSWORD'),
		    'orderNumber' => $request->get('orderNumber'),
		]);

		$gateResponse = $SBERBANK_Gateway->checkOrder();



        $successPayment = true;
        
        if($this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_NUMBER') != $request->get('ORDER_ID')) {
        	$successPayment = false;
        }
		if(explode("_", $gateResponse['orderNumber'] )[0] != $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_NUMBER')) {
			$successPayment = false;
		}

        if( $gateResponse['errorCode'] != 0 || ($gateResponse['orderStatus'] != 5 && $gateResponse['orderStatus'] != 2) ) {
        	$successPayment = false;
        }
        

        if($successPayment && !$payment->isPaid()) {

        	// set payment status
        	$order = Order::load($payment->getOrderId());
			$paymentCollection = $order->getPaymentCollection();
			foreach ($paymentCollection as $col_payment) {
				if($col_payment->getField('ID') == $payment->getField('ID')) {
					$col_payment->setPaid("Y");
					$col_payment->setFields([
		                "PS_SUM" => $gateResponse["amount"] / 100,
		                "PS_CURRENCY" => $gateResponse["currency"],
		                "PS_RESPONSE_DATE" => new DateTime(),
		                "PS_STATUS" => "Y",
		                "PS_STATUS_DESCRIPTION" => $gateResponse["cardAuthInfo"]["pan"] . ";" . $gateResponse['cardAuthInfo']["cardholderName"],
		                "PS_STATUS_MESSAGE" => $gateResponse["paymentAmountInfo"]["paymentState"],
		                "PS_STATUS_CODE" =>  $gateResponse['orderStatus'],
	        		]);

	        		break;
				}
			}
			if($order->isPaid()) {
				// // set order status
				$order->setField('STATUS_ID', Option::get($moduleId, 'RESULT_ORDER_STATUS'));

				// set delivery status
				if($this->getBusinessValue($payment, 'SBERBANK_POKUPAY_HANDLER_SHIPMENT') == 'Y') {
					$shipmentCollection = $order->getShipmentCollection();
					foreach ($shipmentCollection as $shipment){
					    if (!$shipment->isSystem()) {
			        		$shipment->allowDelivery();
					    }
			    	}
		    	}
	    	}
		    $order->save();
        }

        if($gateResponse['errorCode'] == 0) {
		    $db_result = $SBERBANK_Orders->GetByPaymentID($payment->getField('ID'))->Fetch();
		    if($db_result) {
		    	$SBERBANK_Orders->Update(
		    		$db_result['ID'],
		    		[
		    			'BANK_ORDER_ID' => $gateResponse['orderNumber'],
		    			'BANK_MD_ORDER' => $request->get('mdOrder'),
		    			'BANK_SUM' => $gateResponse["amount"] / 100,
		    			'BANK_ORDER_STATUS' => $gateResponse['orderStatus'],
		    			'CMS_ORDER_STATUS' => 'PAYED'
		    		]

		    	);	
		    }
		}

		if($request->get('USER_RETURN')) {
			$APPLICATION->SetTitle(Loc::getMessage('SBERBANK_POKUPAY_MESSAGE_THANKS'));
			echo '<div class="Sberbank-return-page__message" style="margin:20px;text-align:center;">';
				if($payment->isPaid()) {
					echo Loc::getMessage('SBERBANK_POKUPAY_MESSAGE_THANKS') . '<br>';
					echo Loc::getMessage('SBERBANK_POKUPAY_MESSAGE_THANKS_DESCRIPTION') .': #' .  $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_NUMBER');
				} else {
	        		echo Loc::getMessage('SBERBANK_POKUPAY_MESSAGE_PROCESSING') .': #' .  $this->getBusinessValue($payment, 'SBERBANK_POKUPAY_ORDER_NUMBER');
				}
			echo '</div>';
		}
       
        return new PaySystem\ServiceResult();
	}

	public function getPaymentIdFromRequest(Request $request)
	{
	    $paymentId = $request->get('PAYMENT_ID');
	    return intval($paymentId);
	}

	public function getCurrencyList()
	{
		return array('RUB');
	}

	public static function getIndicativeFields()
	{
		return array('PAYMENT' => 'SBERBANK_POKUPAY');
	}

	static protected function isMyResponseExtended(Request $request, $paySystemId)
	{
		$order = Order::load($request->get('ORDER_ID'));
		if(!$order) {
			$order = Order::loadByAccountNumber($request->get('ORDER_ID'));
		} 
		if(!$order) {
			echo Loc::getMessage('SBERBANK_POKUPAY_ERROR_BAD_ORDER');
			return false;
		}

		$paymentIds = $order->getPaymentSystemId();
		return in_array($paySystemId, $paymentIds);
	}

	private function getPropertyValueByCode($propertyCollection, $code) {
		$property = '';
		foreach ($propertyCollection as $property)
	    {
	        if($property->getField('CODE') == $code)
	            return $property->getValue();
	    }
	}


	public function isTuned(){}
	public function isRefundableExtended(){}
	public function confirm(Payment $payment){}
	public function cancel(Payment $payment){}
	public function refund(Payment $payment, $refundableSum){}
	public function sendResponse(PaySystem\ServiceResult $result, Request $request){}

}