<?

$preData = Array(
	'orderNumber' => isset($_REQUEST['orderNumber']) ? $_REQUEST['orderNumber'] : 0,
	'mdOrder' => isset($_REQUEST['mdOrder']) ? $_REQUEST['mdOrder'] : 0,
	'ORDER_ID' => isset($_REQUEST['ORDER_ID']) ? $_REQUEST['ORDER_ID'] : 0,
	'PAYMENT_ID' => isset($_REQUEST['PAYMENT_ID']) ? $_REQUEST['PAYMENT_ID'] : 0,
	'PAYMENT' => 'SBERBANK_POKUPAY',
);


if((!$preData['PAYMENT_ID'] || !$preData['ORDER_ID']) && $preData['orderNumber']) {

	$combinateOrder = explode("_", $preData['orderNumber'] );
    array_pop($combinateOrder);

	$preData['PAYMENT_ID'] = array_pop($combinateOrder);
	$preData['ORDER_ID'] = implode('_', $combinateOrder);

	$redirect_query = '/sberbank/credit_result.php?' . http_build_query($preData);
	header('Location:' . $redirect_query, true, 301);
	die;
}


$objDateTime = new DateTime();
$file = 'callback.log';
$logContent = '';

if(file_exists($file)) {
    $logSize = filesize($file) / 1000;
    if($logSize < 5000) {
        $logContent = file_get_contents($file);
    }
}
$logContent .= 'SBERBANK_CALLBACK.PHP' . "\n";
$logContent .= '----------------------------' . "\n";
$logContent .= "DATE: " . $objDateTime->format("Y-m-d H:i:s") . "\n";
$logContent .= "DATA: \n" . print_r($preData,true) . "\n";
$logContent .= "\n\n";
file_put_contents($file, $logContent);



use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;
use \Bitrix\Sale\Order;
use \Bitrix\Sale\PaySystem;

global $APPLICATION;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);

if (CModule::IncludeModule("sale"))
{
	$context = Application::getInstance()->getContext();
	$request = $context->getRequest();

	$item = PaySystem\Manager::searchByRequest($request);

	if ($item !== false)
	{
		
		$service = new PaySystem\Service($item);

		if ($service instanceof PaySystem\Service)
		{
			$result = $service->processRequest($request);
		}
	}
	else
	{
		$debugInfo = implode("\n", $request->toArray());
		PaySystem\Logger::addDebugInfo('Pay system not found. Request: '.$debugInfo);
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>