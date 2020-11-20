<?php namespace Sberbank\Credit;

use Bitrix\Main\Web;
use DateTime;

define('SBERBANK_POKUPAY_LOG_FILE', realpath(dirname(dirname(dirname(__FILE__)))) . "/logs/sberbank.pokupay.log");

class Gateway
{

    const log_file = SBERBANK_POKUPAY_LOG_FILE;
    /**
     * Массив с НДС
     *
     * @var integer
     * 0 = Без НДС
     * 2 = НДС чека по ставке 10%
     * 3 = НДС чека по ставке 18%
     * 6 = НДС чека по ставке 20%
     */

    private static $arr_tax = [
        0 => 0,
        2 => 10, 
        3 => 18,
        6 => 20,
    ];
    private $gate_url;

	private $basket = [];
	private $data = [];
	private $options = [
		'gate_url_prod' => '',
		'gate_url_test' => '',
		'payment_link' => '',
		'ofd_enabled' => false,
		'module_version' => 'def',
		'language' => 'ru',
		'ofd_tax' => 0,
		'handler_two_stage' => 0,
		'delivery' => false,
		'handler_logging' => true,
		'creditType' => 'CREDIT',
		'test_mode' => 0
	];

	


	public function buildData($data) {
		foreach ($data as $key => $value) {
			$this->data[$key] = $value;
		}
	}

	public function setOptions($data) {
		foreach ($data as $key => $value) {
			$this->options[$key] = $value;
		}
	}

	public function registerOrder() {
		$this->transformPrices();
		$this->buildData([
		    'CMS' => $this->options['cms_version'],
			'language' => $this->options['language'],
		    'jsonParams' => '{"CMS":"'. $this->options['cms_version'] . '", "Module-Version": "' . $this->options['module_version'] . '", "phone": "' . $this->options['customer_phone'] . '"}',
		    'dummy' => $this->options['test_mode'] ? 1 : 0,
		    // 'sessionTimeoutSecs' => 100,
		]);
		$gateData = $this->data;
		$orderId = $this->data['orderNumber'];
		$method = 'register.do';
	 	$gateData['orderNumber'] = $orderId . "_" . time();
	 	
		$gateData = $this->addOrderBundle($gateData);
	 	$gateResponse = $this->setRequest($method, $gateData);
	 	
	 	$this->createPaymentLink($gateResponse['formUrl'],$method);

		
		if($gateResponse['errorCode'] != 0) {
			$this->baseLogger($this->gate_url, $method, $gateData, $gateResponse,'ERROR REGISTER');
		} else {
			$this->baseLogger($this->gate_url, $method, $gateData, $gateResponse,'REGISTER NEW ORDER');	
		}
		return $gateResponse;
	}


	public function checkOrder() {
		$gateData = $this->data;
		$gateResponse = $this->setRequest('getOrderStatusExtended.do', $gateData);

		if($this->options['handler_logging']) {
			$this->baseLogger($this->gate_url, 'getOrderStatusExtended.do', $gateData, Web\Json::encode($gateResponse),'RESULT PAYMENT ORDER');
		}
		return $gateResponse;
	}

	public function ofdEnable() {
		if($this->options['ofd_enabled'] == true) {
			return true;
		}
		return false;
	}

	public function setPosition($position) {
		array_push($this->basket, $position);
	}


	public function getBasket() {        
		return $this->basket;
	}


	public function getTaxCode($tax_rate) {
		$result = 0;
		foreach (self::$arr_tax as $key => $value) {
			if($value == $tax_rate) {
				$result = $key;
			}
		}
		           
		return $result;
	}
	public function getCurrencyCode($currency) {
		$result = 0;
		foreach ($this->options['iso'] as $key => $value) {

			if($key == $currency) {
				$result = $value;
			}
		}
		return $result;
	}



	private function addFFDParams() {
		if($this->options['ffd_version'] == '1.05') {

			foreach ($this->basket as $key => $item) {

				if($this->options['delivery'] && count($this->basket) == $key+1) {
					$paymentMethod = 1;
					$paymentObject = 4;
				} else {
					$paymentMethod = $this->options['ffd_payment_method'];
					$paymentObject = $this->options['ffd_payment_object'];
				}
				$this->basket[$key]['itemAttributes'] = [
	                'attributes' => [
	                    [
	                        'name' => 'paymentMethod',
	                        'value' => $paymentMethod,
	                    ],
	                    [
	                        'name' => 'paymentObject',
	                        'value' => $paymentObject,
	                    ],
	                ]
	            ];
			}

		}
	}
	private function setRequest($method,$data) {

		global $APPLICATION;


		$this->gate_url = $this->options['test_mode'] ?  $this->options['gate_url_test'] : $this->options['gate_url_prod'];
		if($method == 'register.do') {
			$this->gate_url = preg_replace('/payment\/rest/', 'sbercredit', $this->gate_url);
			// $this->gate_url = preg_replace('/sbercredit\//', 'payment/rest/', $this->gate_url);
		}
		
		if (mb_strtoupper(SITE_CHARSET) != 'UTF-8') { $data = $APPLICATION->ConvertCharsetArray($data, 'windows-1251', 'UTF-8'); }
		$http = new Web\HttpClient();
	    $http->setCharset("utf-8");
	 	$http->setHeader('CMS: ', $this->options['cms_version']);
	 	$http->setHeader('Module-Version:: ', $this->options['module_version']);
	 	$http->post($this->gate_url . $method, $data);

	 	$response =  $http->getResult();

	 	if ($this->is_json($response)) {
	    	$response =  Web\Json::decode($response, true);
	    } else {
	        $response = array(
	            'errorCode' => 999,
	            'errorMessage' => 'Server not available',
	        );
	        //var_dump( $http->getError() );
			//var_dump( $http->getStatus() );
			//var_dump( $http->getHeaders() );
	    }

	 	if (mb_strtoupper(SITE_CHARSET) != 'UTF-8') { $APPLICATION->ConvertCharsetArray($response, 'UTF-8', 'windows-1251'); }
	 	
	 	return $response;
	}


	private function addOrderBundle($data) {
		$data['orderBundle']['orderCreationDate'] = '2019-02-12T13:51:00';
		$data['orderBundle']['customerDetails'] = [
			'contact' => $this->options['customer_name'],
			'email' => $this->options['customer_email'],
		];
		$data['orderBundle']['cartItems']['items'] = $this->basket;
		// $data['taxSystem'] = $this->options['ofd_tax'];
		$data['orderBundle']['installments'] = [
			'productType' => $this->options['creditType'],
			'productID' => 10
		];
		$data['orderBundle'] = Web\Json::encode($data['orderBundle']);
		return $data;
	}


	private function transformPrices() {
		$this->data['amount'] = $this->data['amount'] * 100;
		if (is_float($this->data['amount'])) {
		    $this->data['amount'] = round($this->data['amount']);
		}
		if($this->ofdEnable()) {
			foreach ($this->basket as $key => $item) {
				$this->basket[$key]['itemPrice'] = round($item['itemPrice'] * 100);
				$this->basket[$key]['itemAmount'] = round($item['itemAmount'] * 100);
				
			}
		}
	}


	private function createPaymentLink($linkPart,$method) {
		$this->options['payment_link'] = $linkPart;
	}

	private function is_json($string,$return_data = false) {
	      $data = json_decode($string);
	     return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
	}
	public function getPaymentLink() {
		return $this->options['payment_link'];
	}


	public function debug($data) {
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

	
	public function baseLogger($url, $method, $data, $response, $title)
    {
        $objDateTime = new DateTime();
        $file = self::log_file;
        $logContent = '';

        if(file_exists($file)) {
            $logSize = filesize($file) / 1000;
            if($logSize < 5000) {
                $logContent = file_get_contents($file);
            }
        }
        $logContent .= $title . "\n";
        $logContent .= '----------------------------' . "\n";
        $logContent .= "DATE: " . $objDateTime->format("Y-m-d H:i:s") . "\n";
        $logContent .= 'URL ' . $url . "\n";
        $logContent .= 'METHOD ' . $method . "\n";
        $logContent .= "DATA: \n" . print_r($data,true) . "\n";
        $logContent .= "RESPONSE: \n" . print_r($response,true) . "\n";
        $logContent .= "\n\n";
        file_put_contents($file, $logContent);

	}
	public function updateCallback($url) {
		if(!isset($this->data['login']) && !isset($this->data['password'])) {
			return false;
		}
		$headers = array(
            'Content-Type:application/json',
            'Authorization: Basic ' . base64_encode($this->data['login'] . ":" . $this->data['password'])
        );
    	if($this->options['test_mode'] == 1) {
			$gate_url = "https://3dsec.sberbank.ru/mportal-uat/mvc/public/merchant/";
    	} else {
    		$gate_url = "https://securepayments.sberbank.ru/mportal/mvc/public/merchant/";
    	}
    	
    	$gate_url = $gate_url . 'update/' . $this->data['name'];

        $ch = curl_init();
        curl_setopt_array($ch, array(
			//    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $gate_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($this->data),
            CURLOPT_ENCODING, "gzip",
            CURLOPT_ENCODING, '',


        ));
        $response =  curl_exec($ch);

        $this->baseLogger($gate_url, 'update', $this->data, $response,'CALLBACK_UPDATED');
        return true;
	}

}

?>