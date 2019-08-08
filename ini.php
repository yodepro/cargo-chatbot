<?php

/** Путь к файлу лога */
$log_file =  __DIR__.DIRECTORY_SEPARATOR.'events.log';

//$tg_proxy = '5.249.151.24:31280'; //Прокси для Телеграм, если не используется - пустое
//$tg_proxy_user = 'budet:Qw123456'; //Авторизация на прокси (login:pass), если не используется - пустое

/** Настройка проски серверов, если надо */
$tg_proxy = '5.249.151.24:11222';
$tg_proxy_user = 'tg:111222';

/** Токены для месседжеров */
$telegram_token = '672227440:AAGuXZjKbXsfBkSOPUUECr2I64orZgRO9Rg';
$viber_token = '49e4f7ee2967d0ee-579145ba3afc060d-7a907c44e4f312e7';

/** Глобальные переменные */
define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR);


define('RADIUS', '30');             //Радиус поиска по умолчанию
define('BOTNAME', 'ПунктАвто');     //Название бота в сообщениях
define('SUPPORT', '+79998887700');  //Телефон техподдержки - выводится по запросу в боте
define('PRICE', '30');              //Цена за просмотр контакта в рублях
define('PAGE', '10');               //сколько данных в списке за раз (10,20,30,40,50)
define('SORTTYPE', '1');            //тип сортировки 1 - руб\км, 2 - время заявки, 3 - дата загрузки, 4 - по типу груза


/** Настройки БД */
define('A_ADDRESS', 'localhost'); //адрес сервера
define('A_LOGIN', 'cargobot');    //логин
define('A_PASSWORD', 'CDEpLv1Fkkj4WYVs'); //пароль
define('A_NAME', 'cargobot');             //название базы

/**  Настройки UnitPay */
define('UP_PROJECT', '163331');                             //UnitPay projectId
define('UP_SECRET', '15c30d1c9e27cd1bd2a8dafd228e6fd8');    //UnitPay secretKey
define('UP_ID', '163331-a3355');                            //UnitPay publicId
define('UP_CURRENCY', 'RUB');                               //UnitPay валюта



/** Настройки доступа к ati.su */
define('ATIID', '1'); //ID источника, используется для формирования ИД контакта (сдеалано для возможости масштабирования в будущем)
define('ATI_USERAGENT', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0'); //Юзерагент для коннекта
define('ATI_DID', 'giR5yjP1JR2wfdf8plVKenUq8ZhUzXi1oE2037vfbkw='); //DID - авторизующий параметр, срок жизни большой, находить в Cookie
define('ATI_SID', 'a8659edfd5c2410d93b5c5494f17242d'); //SID - авторизующий параметр, срок жизни около месяца, находить в Cookie
define('ATI_GEO_URL', 'https://loads.ati.su/webapi/extendedsearch/v1/geo'); //url запроса идентификации города
define('ATI_SEARCH_URL', 'https://loads.ati.su/webapi/v1.0/loads/search'); //url запроса поиска грузов
define('ATI_SEARCH_TRUCKS_URL', 'https://trucks.ati.su/Tables/Default.aspx'); //url запроса поиска транспорта

/** Справочники данных с сайта ati.su */
//Типы погрузки
define('ATI_TYPES', '{"65536":{"id":0,"name":"не указан","shortName":"не указан","value":65536},"1":{"id":0,"name":"верхняя","shortName":"верх.","value":1},"2":{"id":0,"name":"боковая","shortName":"бок.","value":2},"4":{"id":0,"name":"задняя","shortName":"задн.","value":4},"8":{"id":0,"name":"с полной растентовкой","shortName":"с полн.раст.","value":8},"32":{"id":0,"name":"со снятием поперечных перекладин","shortName":"сн.поп.перекл.","value":32},"64":{"id":0,"name":"со снятием стоек","shortName":"сн.стоек","value":64},"128":{"id":0,"name":"без ворот","shortName":"б.ворот","value":128},"256":{"id":0,"name":"гидроборт","shortName":"гидр.б.","value":256},"512":{"id":0,"name":"аппарели","shortName":"апп.","value":512},"1024":{"id":0,"name":"с обрешеткой","shortName":"реш.","value":1024},"2048":{"id":0,"name":"с бортами","shortName":"борт.","value":2048},"4096":{"id":0,"name":"боковая с 2-х сторон","shortName":"2-бок","value":4096}}');
//Типы транспорта
define('ATI_TRUCK_TYPES', '[{"id":30000,"name":"любой закр.+изотерм","shortName":"закр.+терм.","value":91,"children":[{"id":200,"name":"тентованный","shortName":"тент.","value":1},{"id":100,"name":"контейнер","shortName":"конт.","value":2},{"id":500,"name":"фургон","shortName":"фург.","value":16},{"id":700,"name":"цельнометалл.","shortName":"цмет.","value":64}]},{"id":400,"name":"изотермический","shortName":"изотерм","value":8},{"id":50000,"name":"реф.+изотерм","shortName":"реф.+терм.","value":844424930131980,"children":[{"id":300,"name":"рефрижератор","shortName":"реф.","value":4},{"id":312,"name":"реф. мультирежимный","shortName":"реф.мульт.","value":562949953421312},{"id":310,"name":"реф. с перегородкой","shortName":"реф.с перег.","value":281474976710656}]},{"id":20800,"name":"реф.-тушевоз","shortName":"р-туш.","value":4398046511104},{"id":20000,"name":"все открытые","shortName":"откр.","value":70368744191104,"children":[{"id":1100,"name":"бортовой","shortName":"борт.","value":128},{"id":1150,"name":"открытый конт.","shortName":"откр.конт.","value":1024},{"id":1355,"name":"площадка без бортов","shortName":"безборт.","value":70368744177664},{"id":1200,"name":"самосвал","shortName":"ссвл.","value":4096},{"id":1400,"name":"шаланда","shortName":"шал.","value":8192}]},{"id":5000,"name":"негабарит","shortName":"негаб.","value":18726594281984,"children":[{"id":10500,"name":"низкорамный","shortName":"рамн.","value":512},{"id":10550,"name":"низкорам.платф.","shortName":"нпл.","value":34359738368},{"id":10570,"name":"телескопический","shortName":"телскп.","value":1099511627776},{"id":10700,"name":"трал","shortName":"трал","value":536870912},{"id":20560,"name":"балковоз(негабарит)","shortName":"балк.","value":17592186044416}]},{"id":10800,"name":"автобус","shortName":"авт.","value":16384},{"id":20300,"name":"автовоз","shortName":"автв.","value":32768},{"id":20350,"name":"автовышка","shortName":"вышк.","value":65536},{"id":10100,"name":"автотранспортер","shortName":"автт.","value":131072},{"id":20500,"name":"бетоновоз","shortName":"бет.","value":262144},{"id":20550,"name":"битумовоз","shortName":"битум","value":2199023255552},{"id":20700,"name":"бензовоз","shortName":"бенз.","value":274877906944},{"id":20750,"name":"вездеход","shortName":"вздхд.","value":549755813888},{"id":10600,"name":"газовоз","shortName":"газ.","value":524288},{"id":40000,"name":"зерновоз","shortName":"зерн.","value":1048576},{"id":1280,"name":"коневоз","shortName":"кони.","value":137438953472},{"id":1300,"name":"конт.площадка","shortName":"площ.","value":2097152},{"id":1250,"name":"кормовоз","shortName":"корм.","value":4194304},{"id":10000,"name":"кран","shortName":"кран","value":8388608},{"id":10300,"name":"лесовоз","shortName":"лесв.","value":16777216},{"id":10330,"name":"ломовоз","shortName":"лом.","value":1125899906842624},{"id":1350,"name":"манипулятор","shortName":"манип","value":256},{"id":600,"name":"микроавтобус","shortName":"микр.","value":32},{"id":20200,"name":"муковоз","shortName":"мук.","value":33554432},{"id":10320,"name":"панелевоз","shortName":"панв.","value":2048},{"id":1170,"name":"пикап","shortName":"пикап","value":68719476736},{"id":20860,"name":"пухтовоз","shortName":"пухта","value":140737488355328},{"id":20850,"name":"пирамида","shortName":"пирам.","value":8796093022208},{"id":20870,"name":"рулоновоз","shortName":"рул.","value":35184372088832},{"id":10400,"name":"седельный тягач","shortName":"тягач","value":67108864},{"id":10900,"name":"скотовоз","shortName":"скот.","value":134217728},{"id":10950,"name":"стекловоз","shortName":"сткл.","value":268435456},{"id":10350,"name":"трубовоз","shortName":"труб.","value":1073741824},{"id":20100,"name":"цементовоз","shortName":"цем.","value":2147483648},{"id":10200,"name":"цистерна","shortName":"цист.","value":8589934592},{"id":20150,"name":"щеповоз","shortName":"щеп.","value":4294967296},{"id":20600,"name":"эвакуатор","shortName":"эвак.","value":17179869184},{"id":55000,"name":"грузопассажирский","shortName":"грузпас.","value":2251799813685248},{"id":55500,"name":"клюшковоз","shortName":"клюшк.","value":4503599627370496},{"id":56000,"name":"мусоровоз","shortName":"мусор.","value":9007199254740992},{"id":56500,"name":"юмбо","shortName":"юмбо","value":18014398509481984},{"id":57000,"name":"танк-контейнер 20фут","shortName":"танк-конт.20фут","value":36028797018963968},{"id":57500,"name":"танк-контейнер 40фут","shortName":"танк-конт.40фут","value":72057594037927936}]');

