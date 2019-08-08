<?php


/** Класс получения данных от ati.su
 *
 * Справочники по идшникам на сайте
var truckTypesDictionary = [{"id":30000,"name":"любой закр.$все закр.+изотерм","shortName":"закр.$закр.+терм.","value":91,"children":[{"id":200,"name":"тентованный","shortName":"тент.","value":1},{"id":100,"name":"контейнер","shortName":"конт.","value":2},{"id":500,"name":"фургон","shortName":"фург.","value":16},{"id":700,"name":"цельнометалл.","shortName":"цмет.","value":64}]},{"id":400,"name":"изотермический","shortName":"изотерм","value":8},{"id":50000,"name":"реф.$реф.+изотерм","shortName":"реф.$реф.+терм.","value":844424930131980,"children":[{"id":300,"name":"рефрижератор","shortName":"реф.","value":4},{"id":312,"name":"реф. мультирежимный","shortName":"реф.мульт.","value":562949953421312},{"id":310,"name":"реф. с перегородкой","shortName":"реф.с перег.","value":281474976710656}]},{"id":20800,"name":"реф.-тушевоз","shortName":"р-туш.","value":4398046511104},{"id":20000,"name":"все открытые","shortName":"откр.","value":70368744191104,"children":[{"id":1100,"name":"бортовой","shortName":"борт.","value":128},{"id":1150,"name":"открытый конт.","shortName":"откр.конт.","value":1024},{"id":1355,"name":"площадка без бортов","shortName":"безборт.","value":70368744177664},{"id":1200,"name":"самосвал","shortName":"ссвл.","value":4096},{"id":1400,"name":"шаланда","shortName":"шал.","value":8192}]},{"id":5000,"name":"негабарит","shortName":"негаб.","value":18726594281984,"children":[{"id":10500,"name":"низкорамный","shortName":"рамн.","value":512},{"id":10550,"name":"низкорам.платф.","shortName":"нпл.","value":34359738368},{"id":10570,"name":"телескопический","shortName":"телскп.","value":1099511627776},{"id":10700,"name":"трал","shortName":"трал","value":536870912},{"id":20560,"name":"балковоз(негабарит)","shortName":"балк.","value":17592186044416}]},{"id":10800,"name":"автобус","shortName":"авт.","value":16384},{"id":20300,"name":"автовоз","shortName":"автв.","value":32768},{"id":20350,"name":"автовышка","shortName":"вышк.","value":65536},{"id":10100,"name":"автотранспортер","shortName":"автт.","value":131072},{"id":20500,"name":"бетоновоз","shortName":"бет.","value":262144},{"id":20550,"name":"битумовоз","shortName":"битум","value":2199023255552},{"id":20700,"name":"бензовоз","shortName":"бенз.","value":274877906944},{"id":20750,"name":"вездеход","shortName":"вздхд.","value":549755813888},{"id":10600,"name":"газовоз","shortName":"газ.","value":524288},{"id":40000,"name":"зерновоз","shortName":"зерн.","value":1048576},{"id":1280,"name":"коневоз","shortName":"кони.","value":137438953472},{"id":1300,"name":"конт.площадка","shortName":"площ.","value":2097152},{"id":1250,"name":"кормовоз","shortName":"корм.","value":4194304},{"id":10000,"name":"кран","shortName":"кран","value":8388608},{"id":10300,"name":"лесовоз","shortName":"лесв.","value":16777216},{"id":10330,"name":"ломовоз","shortName":"лом.","value":1125899906842624},{"id":1350,"name":"манипулятор","shortName":"манип","value":256},{"id":600,"name":"микроавтобус","shortName":"микр.","value":32},{"id":20200,"name":"муковоз","shortName":"мук.","value":33554432},{"id":10320,"name":"панелевоз","shortName":"панв.","value":2048},{"id":1170,"name":"пикап","shortName":"пикап","value":68719476736},{"id":20860,"name":"пухтовоз","shortName":"пухта","value":140737488355328},{"id":20850,"name":"пирамида","shortName":"пирам.","value":8796093022208},{"id":20870,"name":"рулоновоз","shortName":"рул.","value":35184372088832},{"id":10400,"name":"седельный тягач","shortName":"тягач","value":67108864},{"id":10900,"name":"скотовоз","shortName":"скот.","value":134217728},{"id":10950,"name":"стекловоз","shortName":"сткл.","value":268435456},{"id":10350,"name":"трубовоз","shortName":"труб.","value":1073741824},{"id":20100,"name":"цементовоз","shortName":"цем.","value":2147483648},{"id":10200,"name":"цистерна","shortName":"цист.","value":8589934592},{"id":20150,"name":"щеповоз","shortName":"щеп.","value":4294967296},{"id":20600,"name":"эвакуатор","shortName":"эвак.","value":17179869184},{"id":55000,"name":"грузопассажирский","shortName":"грузпас.","value":2251799813685248},{"id":55500,"name":"клюшковоз","shortName":"клюшк.","value":4503599627370496},{"id":56000,"name":"мусоровоз","shortName":"мусор.","value":9007199254740992},{"id":56500,"name":"юмбо","shortName":"юмбо","value":18014398509481984},{"id":57000,"name":"танк-контейнер 20фут","shortName":"танк-конт.20фут","value":36028797018963968},{"id":57500,"name":"танк-контейнер 40фут","shortName":"танк-конт.40фут","value":72057594037927936}];
var loadingTypesDictionary = {"65536":{"id":0,"name":"не указан","shortName":"не указан","value":65536},"1":{"id":0,"name":"верхняя","shortName":"верх.","value":1},"2":{"id":0,"name":"боковая","shortName":"бок.","value":2},"4":{"id":0,"name":"задняя","shortName":"задн.","value":4},"8":{"id":0,"name":"с полной растентовкой","shortName":"с полн.раст.","value":8},"32":{"id":0,"name":"со снятием поперечных перекладин","shortName":"сн.поп.перекл.","value":32},"64":{"id":0,"name":"со снятием стоек","shortName":"сн.стоек","value":64},"128":{"id":0,"name":"без ворот","shortName":"б.ворот","value":128},"256":{"id":0,"name":"гидроборт","shortName":"гидр.б.","value":256},"512":{"id":0,"name":"аппарели","shortName":"апп.","value":512},"1024":{"id":0,"name":"с обрешеткой","shortName":"реш.","value":1024},"2048":{"id":0,"name":"с бортами","shortName":"борт.","value":2048},"4096":{"id":0,"name":"боковая с 2-х сторон","shortName":"2-бок","value":4096}};
var cargoTypesDictionary = {"111":"Автомобиль(ли)","1":"Автошины","2":"Алкогольные напитки","98":"Арматура","3":"Безалкогольные напитки","4":"Бумага","5":"Бытовая техника","82":"Бытовая химия","87":"Вагонка","97":"Газосиликатные блоки","96":"Гипс","68":"Гофрокартон","6":"Грибы","101":"Двери","83":"ДВП","102":"Домашний переезд","80":"Доски","7":"Древесина","8":"Древесный уголь","60":"ДСП","95":"ЖБИ","9":"Зерно и семена (в упаковке)","92":"Зерно и семена (насыпью)","90":"Игрушки","10":"Изделия из кожи","11":"Изделия из металла","36":"Изделия из резины","105":"Инструмент","78":"Кабель","12":"Казеин","13":"Канц. товары","62":"Кирпич","14":"Ковры","15":"Компьютеры","77":"Кондитерские изделия","16":"Консервы","84":"Контейнер 20фут","17":"Контейнер 40фут","89":"Кормовые/пищевые добавки","85":"Крупа","64":"ЛДСП","106":"Люди","18":"Макулатура","19":"Мебель","20":"Медикаменты","108":"Мел","21":"Металл","22":"Металлолом","86":"Металлопрокат","66":"Минвата","23":"Молоко сухое","24":"Мороженое","71":"Мука","25":"Мясо","26":"Нефтепродукты","27":"Оборудование и запчасти","91":"Оборудование медицинское","28":"Обувь","29":"Овощи","103":"Огнеупорная продукция","30":"Одежда","31":"Парфюмерия и косметика","67":"Пенопласт","109":"Песок","32":"Пиво","81":"Пиломатериалы","33":"Пластик","73":"Поддоны","34":"Продукты питания","206":"Профлист","35":"Птица ","37":"Рыба (неживая)","38":"Сантехника","39":"Сахар","40":"Сборный груз","75":"Соки","107":"Соль","41":"Стекло и фарфор","70":"Стеклотара (бутылки и др.)","42":"Стройматериалы","100":"Сэндвич-панели","43":"Табачные изделия","207":"Танк-контейнер 20фут","208":"Танк-контейнер 40фут","44":"Тара и упаковка","45":"Текстиль","46":"ТНП","47":"Торф","50":"Транспортные средства","63":"Трубы","51":"Удобрения","61":"Утеплитель","65":"Фанера","88":"Ферросплавы","52":"Фрукты","54":"Хим. продукты неопасные","53":"Хим. продукты опасные","55":"Хозтовары","79":"Холодильное оборудование","93":"Цветы","76":"Цемент","74":"Чипсы","56":"Шкуры мокросоленые","94":"Шпалы","110":"Щебень","57":"Электроника","58":"Ягоды","59":"Другой"};
var currencyTypesDictionary = {"1":"руб","12":"тыс.руб","8":"руб/км","19":"руб/час","20":"руб/куб","13":"руб/т.","5":"грн","15":"тыс.грн","10":"грн/км","16":"грн/т.","2":"usd","9":"usd/км","17":"usd/т.","3":"eur","11":"eur/км","18":"eur/т.","6":"wmr","4":"wmz","14":"wmu","7":"янд","21":"kzt","24":"kzt/км","25":"kzt/т.","22":"бел.руб","23":"уз.сум","26":"лари","27":"лари/т.","28":"лари/км","29":"kgs","30":"kgs/т.","31":"kgs/км","32":"byn","33":"byn/т.","34":"byn/км"};
var packTypesDictionary = {"1":"навалом","2":"коробки","3":"россыпью","4":"палеты","5":"пачки","6":"мешки","7":"биг-бэги","8":"ящики","9":"листы","10":"бочки","11":"канистры","12":"рулоны","13":"пирамида","14":"еврокубы","15":"катушки","16":"барабаны"};
var moneyTypesDictionary = {"1":"нал","22":"любая","23":"на карту","24":"б/нал с НДС","25":"б/нал без НДС"};
var userDefaultItemsNumber = 10;
var userDefaultFilter = {"fromRadius":0,"exactFromGeos":false,"toRadius":0,"exactToGeos":false,"ellipse":{"enabled":false,"maxEnlargment":0,"maxEnlargmentUnit":1,"minLength":0,"minLengthUnit":1,"units":[{"key":1,"value":"%"},{"key":2,"value":"км"}]},"routeParams":{"enabled":false},"weight":{},"volume":{},"length":{},"height":{},"width":{},"dateOption":"today-plus","rateType":1,"truckType":0,"loadingType":0,"extraParams":0,"dogruz":0,"firmRating":0,"firmListsExclusiveMode":false,"cargoTypes":[],"firmListsInclusive":[],"firmListsExclusive":[],"hideHiddenLoads":true,"sortingType":2,"changeDate":0,"boardList":[],"withDimensions":false,"withAuction":false};
var defaultRadiusFrom = 0;
var defaultRadiusTo = 0;
var acceptPaymentTypesDictionary = {"0":"","1":"наличные","2":"безнал с НДС","4":"безнал без НДС","6":"безнал с НДС, без НДС"};
 */

class ati {


    /** Получаем ид города по свободному поиску (по слову)*/
    public function getGeoId($string=""){
        global $kernel;

        $string = trim($string);
//Формируем заголовки
        $headers[] = "Content-Type:application/json;charset=utf-8";
        $headers[] = "User-Agent: ".ATI_USERAGENT."";
        $headers[] = "Cookie: itemsPerPage=10;did=".ATI_DID."; sid=".ATI_SID."";

//Формируем тело запроса
        $post = '{"prefix":"'.$string.'","geo_types":23}';

//Запрашиваем данные на сервере
        $request = $kernel->curlPost(ATI_GEO_URL,$post,$headers);

        if (is_numeric($request)) {
            switch ($request){

                case 400:
                    $message = "Ошибка 400 (неверный запрос) на сайте ati.su, критично, возможно изменились способы запроса";
                    break;

                case 401:
                    $message = "Ошибка 401 (неавторизован) на сайте ati.su, критично, не подходят учетные данные или изменились способы запроса";
                    break;

                case 403:
                    $message = "Ошибка 403 (доступ запрещен) на сайте ati.su, критично, не подходят учетные данные или изменились способы запроса";
                    break;

                case 404:
                    $message = "Ошибка 404 (не найдена страница) на сайте ati.su, критично, возможно изменились способы запроса";
                    break;

                case 500:
                    $message = "Ошибка 500 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 502:
                    $message = "Ошибка 502 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 503:
                    $message = "Ошибка 503 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 0:
                    $message = "Ошибка 0 (не установили соединение) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                default:
                    $message = "Ошибка ".$request." (не предусмотрена в программе) на сайте ati.su";
                    break;
            }
            $kernel->alertMessage($message);

        }


        $result = json_decode($request, true);

//Фиксим возможный затык на дефисе
        if (empty($result)) {
            $string = preg_replace("/  +/"," ",$string);
            $string = str_replace(' ','-',$string);
            $post = '{"prefix":"'.$string.'","geo_types":23}';
            $request = $kernel->curlPost(ATI_GEO_URL,$post,$headers);
            $result = json_decode($request, true);
        }

//Проверяем JSON на валидность:
        if (json_last_error() === JSON_ERROR_NONE) {
            // JSON верный

            foreach ($result as $item) {
                if ((isset($item['id'])) and (isset($item['type']))) {
                    return [
                        'id'=>$item['id'],
                        'type'=>$item['type']
                    ];
                } else {return false;}
                break; //Берем только первое соответсвие
            }
        } else {return false;}
    }

    /** Получаем результаты поиска транспорта по параметрам поиска (парсер страницы)*/
    public function getSearchTruck($from,$to,$weight,$radius=0,$page=1,$count=false){
        global $kernel;

        //Подключаем бибилиотеку парсера
        include_once(DOC_ROOT.'SimpleHTML/simple_html_dom.php');

        //Собираем урл запроса
        $url = ATI_SEARCH_TRUCKS_URL;
        $request_params = array(
            'EntityType' => 'Truck',
            'FromGeo' => $from['type'].'_'.$from['id'],
            'FromGeoRadius' => $radius,
            'ToGeo' => $to['type'].'_'.$to['id'],
            'ToGeoRadius' => $radius,
            'Weight' => $weight,
            'ExactFromGeos'=>'true',
            'ExactToGeos'=>'true',
            'qdsv'=>0,
            'FirstDate'=>strval(date('Y-m-d',time())),
            'LastDate'=>strval(date('Y-m-d',time())),
        );

        if ($page > 1) {$request_params['PageNumber'] = $page;}

        $get_params = http_build_query($request_params);

        $url = $url.'?'.$get_params;

        //$kernel->fnlog($url);

        //$headers[] = "Host: trucks.ati.su";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers[] = "Content-Type:application/json;charset=utf-8";
        $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0";
        $headers[] = "Cookie: itemsPerPage=".PAGE.";did=".ATI_DID."; sid=".ATI_SID.";VisitorSettings=Truck_RecordsCount=".PAGE."";

        //Запрашиваем страницу на сайте
        $request = $kernel->curlGet($url,$headers);

        if (is_numeric($request)) {
            switch ($request){

                case 400:
                    $message = "Ошибка 400 (неверный запрос) на сайте ati.su, критично, возможно изменились способы запроса";
                    break;

                case 401:
                    $message = "Ошибка 401 (неавторизован) на сайте ati.su, критично, не подходят учетные данные или изменились способы запроса";
                    break;

                case 403:
                    $message = "Ошибка 403 (доступ запрещен) на сайте ati.su, критично, не подходят учетные данные или изменились способы запроса";
                    break;

                case 404:
                    $message = "Ошибка 404 (не найдена страница) на сайте ati.su, критично, возможно изменились способы запроса";
                    break;

                case 500:
                    $message = "Ошибка 500 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 502:
                    $message = "Ошибка 502 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 503:
                    $message = "Ошибка 503 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 0:
                    $message = "Ошибка 0 (не установили соединение) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                default:
                    $message = "Ошибка ".$request." (не предусмотрена в программе) на сайте ati.su";
                    break;
            }
            $kernel->alertMessage($message);
            return '';
        }

        //меняем кодировку ответ, по умолчанию на источнике 1251, нам нужна UTF-8
        $request = iconv("CP1251", "UTF-8", $request);



        //парсим ответ
        $html = str_get_html($request);

        if ($html->find('span[id=lblRowsCount]', 0)) {
            $countTruck = $html->find('span[id=lblRowsCount]', 0)->plaintext;
            $countTruck = intval(str_replace('Количество найденных машин ', '', $countTruck));
        } else {$countTruck = 0;}
        if ($count == true) {
            return $countTruck;
        }

        $resultArr = [];
        $resultArr['totalCount'] = $countTruck;

        $id = '';
        $truckType = '';
        $load = '';
        $unload = '';
        $price = '';

        //Если есть таблица с машинами - продолжаем
        if($html->innertext!='' and count($html->find('table .atiTables'))){


            foreach($html->find('table .atiTables',0)->find('tr') as $tr){


                //firstRow - информация по машине 1 блок
                //secondRow - контактная информация по машине 2 блок

                if (stripos($tr->class, 'firstRow')  !== false) {

                    $id = '';
                    $truckType = '';
                    $load = '';
                    $unload = '';
                    $price = '';
                    $note = '';
                    $UserId = '';
                    $ContactIDs = [];


                    //echo " 1-- ".$tr->class.'</br>';
                    //echo " id -- ".$tr->combinedid.'</br>';

                    $id = $kernel->saveIDtoCash($tr->combinedid);

                    $poleType = [
                        'input','country','trackType','load','unload','price'
                    ];
                    $nn = 0;

                    $tr->find('table',0)->outertext = '';
                    $tr->find('#divReq_'.$tr->combinedid,0)->outertext = '';

                    foreach(str_get_html($tr->outertext)->find('td') as $td){
                        if ($td->find('a',0)) {$td->find('a',0)->outertext = '';}
                        //echo $poleType[$nn]." ".$td->plaintext.'</br>';

                        if (!isset($poleType[$nn])) {continue;}

                        switch ($poleType[$nn]){

                            case'trackType':
                                //echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                                $truckType = trim($td->plaintext);
                                $nn++;
                                break;

                            case'load':
                                //echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                                $load = trim($td->plaintext);
                                $nn++;
                                break;

                            case'unload':

                                //echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                                $unload = trim($td->plaintext);
                                $nn++;
                                break;

                            case'price':

                                //echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                                $price = trim($td->plaintext);
                                $nn++;
                                break;

                            default:
                                $nn++;
                                break;
                        }


                    }


                } elseif (stripos($tr->class, 'secondRow')  !== false) {

                    $note = '';
                    $firm = '';
                    $profile = '';
                    $city = '';
                    $firm_id = '';
                    $ContactIDs = [];


                    if ($tr->find('table[id*=tblNote] .noteText', 0)) {

                        //echo "Комментарий: " . $tr->find('table[id*=tblNote] .noteText', 0)->plaintext . '</br>';
                        $note = trim($tr->find('table[id*=tblNote] .noteText', 0)->plaintext);
                    }

                    //Если есть записи о контактах
                    if ($tr->find('table[id*=tblFirm]', 0)) {

                        if($tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td',0)) {
                           // echo "Фирма: " . $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td', 0)->plaintext . '</br>';
                            $firm = trim($tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td', 0)->plaintext);
                        } else {$firm = '';}


                        if($tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td',1)->plaintext){

                            $match = preg_match( '#\((.+?)\)#is', $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td',1)->plaintext, $details );

                            if ($match) {
                                $details = explode(',',$details[1]);

                                if (count($details) >= 3) {

                                    //echo 'Роль: '.$details[1].'<br>';
                                    //echo 'Город: '.$details[2].'<br>';
                                    $firm_id = str_replace('ID:','',trim($details[0]));
                                    $profile = trim($details[1]);
                                    $city = trim($details[2]);
                                }

                                if ($tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('span[id*=ctlStarControl]',0)->title) {

                                    $match = preg_match( '#Балл участника АТИ: (.+?). #is', $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('span[id*=ctlStarControl]',0)->title, $details );
                                    if ($match) {
                                        //echo 'Рейтинг: '.$details[1].'<br>';
                                        $rating = intval($details[1]);

                                    } else {$rating = '';}
                                }

                                //echo "Детали: " . $details[1];
                            }

                        } else {$profile = '';$city = '';$rating='';}

                        //echo "Рейтинг: " . $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('span[id*=ctlStarControl]',0)->title . '</br>';
                        //ctlStarControl
                    }

                    //Если есть контактные данные
                    if ($tr->find('table[id*=tblContacts]',0)->find('table[id*=ContactsData]')) {

                        foreach ($tr->find('table[id*=tblContacts]',0)->find('table[id*=Contacts]') as $contact) {

                            if ($contact->find('td')) {

                                foreach ($contact->find('td') as $contTd) {

                                    if ($contTd->find('input[id*=itFirmInfo]',0)) {

                                        //$kernel->fnlog('найден инпут ');$UserId = '';

                                        $match = preg_match( "/https:\/\/(.+?u=\d{1,}).+,./is", $contTd->find('input[id*=itFirmInfo]',0)->onclick, $link );

                                        if ($match) {
                                            $url = parse_url($link[1]);
                                            $get_string = $url['query'];
                                            parse_str(html_entity_decode($get_string), $get_array);



                                            if (isset($get_array['u'])) {$UserId = $get_array['u'];} else {

                                                $UserId = '';}
                                        } else {$UserId = '';}
                                        continue;
                                    }

                                    if ($contTd->find('span[class*=faxText]')) {continue;}

                                    if ($contTd->find('a[class=PhoneNumberRef]')) {

                                        $texts = explode(',',$contTd->plaintext);
                                        If (isset($texts[count($texts)-1])) {

                                            $match = preg_match("/[\d]+/", $texts[count($texts)-1], $matches);
                                            if ($match) {
                                                if (strlen($matches[0]) < 2) {
                                                    //echo "Имя " . $texts[count($texts) - 1] . '</br>';
                                                    $name = trim($texts[count($texts)-1]);
                                                } else {$name = '';}
                                            } else {$name = trim($texts[count($texts)-1]);
                                            //echo "Имя " . $texts[count($texts) - 1] . '</br>';
                                                }
                                        } else {$name = '';}

                                        if ($contact->find('td')) {

                                        $PhoneNumbers = [];
                                        foreach ($contTd->find('a[class=PhoneNumberRef]') as $phone) {
                                            //echo "Телефон " . $phone->plaintext . '</br>';
                                            $PhoneNumbers[] = trim($phone->plaintext);
                                        }





                                    }

                                        $idContact = '1_'.$firm_id.'_'.$UserId;

                                        $ContactIDs[] = $kernel->saveContact(
                                            [
                                                'id'=>$idContact,
                                                'firmName'=>$firm,
                                                'city'=>$city,
                                                'profile'=>$profile,
                                                'nameContact'=>$name,
                                                'phones' => JSON_ENCODE($PhoneNumbers,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                                            ]

                                        );



                                } //Перебор контактов

                            }



                        }


                    } //контактные данные



                }

                    if (!empty($ContactIDs)) {


                        //$kernel->fnlog('Добавляем в массив');

                        $resultArr[] = [

                            'id'=>$id,
                            'truckType'=> preg_replace("/  +/"," ",trim($truckType)),
                            'load'=>preg_replace("/  +/"," ",trim($load)),
                            'unload'=>preg_replace("/  +/"," ",trim($unload)),
                            'price'=>preg_replace("/  +/"," ",trim($price)),
                            'rating'=>$rating,
                            'note'=>preg_replace("/  +/"," ",trim($note)),
                            'contactsID' => $ContactIDs,
                        ];
                    }



            } //Блок контактов




        } //Перебор таблицы

            //Возвращаем массив результатов парсинга
            return $resultArr;

        $html->clear(); // подчищаем за собой
        unset($html);



return '';
    } elseif ($countTruck > 0)  {
        $kernel->alertMessage('Произошла ошибка парсера страницы грузов, возможно изменилась структура страницы сайта ati.su');
        }
return '';
    }

    /** Получаем результаты поиска груза по параметрам поиска */
    public function getSearchCargo($from,$to,$weight,$radius=0,$page=1,$count=false){
        global $kernel;

        //Формируем заголовки
        $headers[] = "Content-Type:application/json;charset=utf-8";
        $headers[] = "User-Agent: ".ATI_USERAGENT."";
        $headers[] = "Cookie: itemsPerPage=".PAGE.";did=".ATI_DID."; sid=".ATI_SID."";

        //Параметры запроса к API
        $post = array (
            'page' => $page,
            'items_per_page' => PAGE,
            'filter' =>
                array (
                    'from' =>
                        array (
                            'id' => $from['id'],
                            'type' => $from['type'],
                            'list_id' => NULL,
                            'list_type' => NULL,
                            'exact_only' => true,
                            'radius' => $radius,
                        ),
                    'to' =>
                        array (
                            'id' => $to['id'],
                            'type' => $to['type'],
                            'list_id' => NULL,
                            'list_type' => NULL,
                            'exact_only' => true,
                            'radius' => $radius,
                        ),
                    'weight' =>
                        array (
                            'max' => $weight,
                        ),
                    'dates' =>
                        array (
                            'date_option' => 'manual',
                            'date_from' => strval(date('Y-m-d',time())),
                            'date_to' => NULL,
                        ),
                    'truck_type' => 0,
                    'loading_type' => 0,
                    'extra_params' => 0,
                    'dogruz' => NULL,
                    'sorting_type' => SORTTYPE,
                    'change_date' => 0,
                    'show_hidden_loads' => false,
                    'board_list' =>
                        array (
                        ),
                    'with_dimensions' => false,
                    'with_auction' => false,
                ),
            'exclude_geo_dicts' => false,
        );

//Запрашиваем данные на сервере
        $request = $kernel->curlPost(ATI_SEARCH_URL,JSON_ENCODE($post),$headers);

        $kernel->fnlog($request);

        if (is_numeric($request)) {
            switch ($request){

                case 400:
                    $message = "Ошибка 400 (неверный запрос) на сайте ati.su, критично, возможно изменились способы запроса";
                    break;

                case 401:
                    $message = "Ошибка 401 (неавторизован) на сайте ati.su, критично, не подходят учетные данные или изменились способы запроса";
                break;

                case 403:
                    $message = "Ошибка 403 (доступ запрещен) на сайте ati.su, критично, не подходят учетные данные или изменились способы запроса";
                    break;

                case 404:
                    $message = "Ошибка 404 (не найдена страница) на сайте ati.su, критично, возможно изменились способы запроса";
                break;

                case 500:
                    $message = "Ошибка 500 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                break;

                case 502:
                    $message = "Ошибка 502 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 503:
                    $message = "Ошибка 503 (ошибка сервера) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                case 0:
                    $message = "Ошибка 0 (не установили соединение) на сайте ati.su, не критично, скорее всего проблемы на сайте";
                    break;

                default:
                    $message = "Ошибка ".$request." (не предусмотрена в программе) на сайте ati.su";
                    break;
            }
            $kernel->alertMessage($message);
        }

        $result = json_decode($request, true);

        //$kernel->fnlog($result);

//Проверяем JSON на валидность:
        if (json_last_error() === JSON_ERROR_NONE) {
            // JSON верный
            $resultArr['totalCount'] = $result['totalItems'];

            if ($count == true) {return $result['totalItems'];}

            //получаем массив типов погрузки\разгрузки
            $loadingTypesDic =  JSON_DECODE(ATI_TYPES,true);
            $loadingTypes = [];
            foreach ($loadingTypesDic as $type) {
                $loadingTypes[$type['value']] = $type['shortName'];

            }


            //получаем массив типов машин
            $truckTypesDic = JSON_DECODE(ATI_TRUCK_TYPES,true);
            $truckTypes = [];
            foreach ($truckTypesDic as $type) {
                $truckTypes[$type['value']] = $type['shortName'];
                if (isset($type['children'])) {
                    foreach ($type['children'] as $child) {
                        $truckTypes[$child['value']] = $child['shortName'];
                    }
                }
            }

            //перебираем массив грузов
            foreach ($result['loads'] as $item) {

                $ContactIDs = [];

                //Собираем контактные данные
                foreach ($item['firm']['contacts'] as $contact) {

                    $idContact = ATIID.'_'.$item['firm']['id'].'_'.$contact['id'];
                    $firmName = $item['firm']['firmFullName'];
                    $firmCity = $item['firm']['city'];
                    $profile = $item['firm']['profile'];
                    $nameContact = $contact['name'];
                    $phoneNum = [];
                    foreach ($contact['phones'] as $phones){
                        $phoneNum[] = $phones['number'];
                    }

                    //$kernel->fnlog($idContact.' '.$firmName.' '.$nameContact);

                    $ContactIDs[] = $kernel->saveContact(
                        [
                            'id'=>$idContact,
                            'firmName'=>$firmName,
                            'city'=>$firmCity,
                            'profile'=>$profile,
                            'nameContact'=>$nameContact,
                            'phones' => JSON_ENCODE($phoneNum,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        ]

                    );

                }

                //кэшируем ид записи в БД
                $id = $kernel->saveIDtoCash($item['id']);

                $loadTypes = [];
                $unloadTypes = [];
                if (!empty($item['truck']['loadingTypes'])) {$loadTypes = $item['truck']['loadingTypes'];}
                if (!empty($item['truck']['unloadingTypes'])) {$unloadTypes = $item['truck']['unloadingTypes'];}
                $CombTypes = array_unique(array_merge($loadTypes,$unloadTypes));
                $CombTypesStr = '';
                foreach ($CombTypes as $type) {
                  if (array_key_exists($type,$loadingTypes)) {$CombTypesStr .= $loadingTypes[$type].' ';}
                }

                $truckTypesSum = '';
                $truckTypesArr = [];
                if (!empty($item['truck']['carTypesBitSum'])) {$truckTypesSum = $item['truck']['carTypesBitSum'];}
                if (!empty($item['truck']['carTypes'])) {$truckTypesArr = $item['truck']['carTypes'];}
                $TruckTypesStr = '';
                if (array_key_exists($truckTypesSum,$truckTypes)) {$TruckTypesStr .= $truckTypes[$truckTypesSum].' ';}
                else {

                    foreach ($truckTypesArr as $type) {

                        if (array_key_exists($type,$truckTypes)) {$TruckTypesStr .= $truckTypes[$type].' ';}

                    }

                }




                //формируем массив данных
                $resultArr[] = [
                'id' => $id,
                'start' => $item['loading']['location']['fullGeoName'],
                'end'  => $item['unloading']['location']['fullGeoName'],
                'type' => $item['load']['cargoType'],
                'loadType' => (empty($CombTypesStr)) ? 'Не указано' : $CombTypesStr,
                'TruckType' => (empty($TruckTypesStr)) ? 'Не указано' : $TruckTypesStr,
                'weight' => ($item['load']['weight'] == 0) ? 'Не указан' : $item['load']['weight'],
                'volume' => ($item['load']['volume'] == 0) ? 'Не указан' : $item['load']['volume'],
                'price' => ($item['rate']['price'] == 0) ? 'Не указана' : $item['rate']['price'],
                'priceNoNds' => ($item['rate']['priceNoNds'] == 0) ? 'Не указана' : $item['rate']['priceNoNds'],
                //'priceNoNds' => $item['rate']['priceNoNds'],
                'priceNds' => ($item['rate']['priceNds'] == 0) ? 'Не указана' : $item['rate']['priceNds'],
                //'priceNDS' => $item['rate']['priceNds'],
                'rating' => $item['firm']['rating']['score'],
                'note' => (empty($item['note'])) ? 'нет' : $item['note'],
                'contactsID' => $ContactIDs,
                ];





            }


            //возвращаем массив полученных данных
            return $resultArr;
        } else {return '';}

    }



}