<?php

/**
 *  Handler for UnitPay
 *
 *
 *
 *
 */

//Пишем все ошибки php в файл
ini_set('log_errors', 'On');
ini_set('display_errors', 'Off');
ini_set('error_log', __DIR__.DIRECTORY_SEPARATOR.'handler_errors.log');

require_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'ini.php'); //считываем базовую конфигурацию
require_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'autoloader.php'); //подключаем классы
$kernel = new kernel(); //Объявляем класс ядра

//$kernel->fnlog('Сообщение о платеже');

//require_once('./orderInfo.php');
require_once('../UnitPay.php');

$unitPay = new UnitPay(UP_SECRET);

try {
    // Validate request (check ip address, signature and etc)
    $unitPay->checkHandlerRequest();

    list($method, $params) = array($_GET['method'], $_GET['params']);

    //$kernel->fnlog($params);

    $order = $kernel->getPayorder($params['account']);



    $orderSum = $order['orderSum'];
    $orderCurrency = $order['orderCurrency'];
    $orderId = $order['id'];
    $projectId = UP_PROJECT;
    $userId = $order['userId'];


    // Very important! Validate request with your order data, before complete order
    if (
        $params['orderSum'] != $orderSum ||
        $params['orderCurrency'] != $orderCurrency ||
        $params['account'] != $orderId ||
        $params['projectId'] != $projectId
    ) {
        // logging data and throw exception
        throw new InvalidArgumentException('Order validation Error!');
    }

    switch ($method) {
        // Just check order (check server status, check order in DB and etc)
        case 'check':
            if (($order['orderStatus']) == 'created'){
            print $unitPay->getSuccessHandlerResponse('Check Success. Ready to pay.');}
            else {
                print $unitPay->getErrorHandlerResponse('Счет не готов к оплате!');
                $kernel->fnlog('Счет не готов к оплате! ' . $orderId);
                $kernel->fnlog($params);
            }
            break;
        // Method Pay means that the money received
        case 'pay':
            // Please complete order
            if ($kernel->updateOrder($orderId,'done',
                JSON_ENCODE($params,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),$userId,$orderSum,true)) {
                print $unitPay->getSuccessHandlerResponse('Pay Success');
            } else {
                print $unitPay->getErrorHandlerResponse('Не удалось пополнить баланс!');
                $kernel->fnlog('Не удалось пополнить баланс! '.$orderId);
                $kernel->fnlog($params);
            }
            break;
        // Method Error means that an error has occurred.
        case 'error':
            // Please log error text.
            $kernel->fnlog('Получена ошибка от UnitPay! '.$orderId);
            $kernel->fnlog($params);
            print $unitPay->getSuccessHandlerResponse('Error logged');
            break;
        // Method Refund means that the money returned to the client
        case 'refund':
            // Please cancel the order
            if ($kernel->updateOrder($orderId,'canceled',JSON_ENCODE($params,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))) {
                print $unitPay->getSuccessHandlerResponse('Order canceled');
            } else {
                $kernel->fnlog('Не удалось отменить счет! '.$orderId);
                $kernel->fnlog($params);
                print $unitPay->getErrorHandlerResponse('Не удалось отменить счет!');
            }
            break;
    }
// Oops! Something went wrong.
} catch (Exception $e) {
    print $unitPay->getErrorHandlerResponse($e->getMessage());
}
