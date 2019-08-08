<?php

//Пишем все ошибки php в файл
ini_set('log_errors', 'On');
ini_set('display_errors', 'Off');
ini_set('error_log', __DIR__.DIRECTORY_SEPARATOR.'php_errors.log');

require_once (__DIR__.DIRECTORY_SEPARATOR.'ini.php'); //считываем базовую конфигурацию
require_once (__DIR__.DIRECTORY_SEPARATOR.'autoloader.php'); //подключаем классы
$kernel = new kernel(); //Объявляем класс ядра

//парсим входящий URL
$url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_parts = explode('/', trim($url_path, ' /'));

//Если это телеграм
if (((count($uri_parts)) == 1) and  ($uri_parts[0] == $telegram_token)) {

//Если это запрос сам на себя - разбираем сообщение, если нет - отдаем 200 и перезапрашиваем
    if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
        header("HTTP/1.0 200 OK");
        $class = new telegram();
        $class->incoming();
        exit;
    } else {
        header("HTTP/1.0 200 OK");
        $url = "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $kernel->RetransferData($url,file_get_contents('php://input',true));
        exit;
    }
}

//Регистрация текущего домена для weebHook Viber
if (((count($uri_parts)) == 1) and  ($uri_parts[0] == "viber_webhook_enable")) {
    header("HTTP/1.0 200 OK");
    $class = new viber();
    $class->webhookRegister();
    exit;
}

//Если это Viber
if (((count($uri_parts)) == 1) and  ($uri_parts[0] == $viber_token)) {


//Если это запрос сам на себя - разбираем сообщение, если нет - отдаем 200 и перезапрашиваем
    if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
        header("HTTP/1.0 200 OK");
        $class = new viber();
        $class->incoming();
        exit;
    } else {
        header("HTTP/1.0 200 OK");
        $url = "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $kernel->RetransferData($url,file_get_contents('php://input',true));
        exit;
    }

}


//Для проверки домена для Unitpay  52.29.152.23
if (($_SERVER['REMOTE_ADDR'] == '31.186.100.49') OR ($_SERVER['REMOTE_ADDR'] == '178.132.203.105') OR ($_SERVER['REMOTE_ADDR'] == '52.29.152.23')) {
    header("HTTP/1.0 200 OK");

    print ('
<html>
<head>
<title>OK</title>
<meta name="verification" content="f612c7d25f5690ad41496fcfdbf8d1" /> 
</head>
<body bgcolor="white">
</body>
</html>
');

exit;
}

//по умолчанию отказ
$kernel->deny();







