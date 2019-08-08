<?php

ini_set('log_errors', 'On');
ini_set('display_errors', 'Off');
ini_set('error_log', __DIR__.DIRECTORY_SEPARATOR.'php_errors.log');


require_once (__DIR__.DIRECTORY_SEPARATOR.'ini.php'); //считываем базовую конфигурацию
require_once (__DIR__.DIRECTORY_SEPARATOR.'autoloader.php'); //подключаем классы
$kernel = new kernel(); //Объявляем класс ядра

/*
$loadingTypesDic = '{"65536":{"id":0,"name":"не указан","shortName":"не указан","value":65536},"1":{"id":0,"name":"верхняя","shortName":"верх.","value":1},"2":{"id":0,"name":"боковая","shortName":"бок.","value":2},"4":{"id":0,"name":"задняя","shortName":"задн.","value":4},"8":{"id":0,"name":"с полной растентовкой","shortName":"с полн.раст.","value":8},"32":{"id":0,"name":"со снятием поперечных перекладин","shortName":"сн.поп.перекл.","value":32},"64":{"id":0,"name":"со снятием стоек","shortName":"сн.стоек","value":64},"128":{"id":0,"name":"без ворот","shortName":"б.ворот","value":128},"256":{"id":0,"name":"гидроборт","shortName":"гидр.б.","value":256},"512":{"id":0,"name":"аппарели","shortName":"апп.","value":512},"1024":{"id":0,"name":"с обрешеткой","shortName":"реш.","value":1024},"2048":{"id":0,"name":"с бортами","shortName":"борт.","value":2048},"4096":{"id":0,"name":"боковая с 2-х сторон","shortName":"2-бок","value":4096}}';
$loadingTypesDic =  JSON_DECODE($loadingTypesDic,true);
$loadingTypes = [];
foreach ($loadingTypesDic as $type) {
    $loadingTypes[$type['value']] = $type['shortName'];
}

var_dump($loadingTypes);
*/

/*

$TrackTypesDic = '[{"id":30000,"name":"любой закр.$все закр.+изотерм","shortName":"закр.$закр.+терм.","value":91,"children":[{"id":200,"name":"тентованный","shortName":"тент.","value":1},{"id":100,"name":"контейнер","shortName":"конт.","value":2},{"id":500,"name":"фургон","shortName":"фург.","value":16},{"id":700,"name":"цельнометалл.","shortName":"цмет.","value":64}]},{"id":400,"name":"изотермический","shortName":"изотерм","value":8},{"id":50000,"name":"реф.$реф.+изотерм","shortName":"реф.$реф.+терм.","value":844424930131980,"children":[{"id":300,"name":"рефрижератор","shortName":"реф.","value":4},{"id":312,"name":"реф. мультирежимный","shortName":"реф.мульт.","value":562949953421312},{"id":310,"name":"реф. с перегородкой","shortName":"реф.с перег.","value":281474976710656}]},{"id":20800,"name":"реф.-тушевоз","shortName":"р-туш.","value":4398046511104},{"id":20000,"name":"все открытые","shortName":"откр.","value":70368744191104,"children":[{"id":1100,"name":"бортовой","shortName":"борт.","value":128},{"id":1150,"name":"открытый конт.","shortName":"откр.конт.","value":1024},{"id":1355,"name":"площадка без бортов","shortName":"безборт.","value":70368744177664},{"id":1200,"name":"самосвал","shortName":"ссвл.","value":4096},{"id":1400,"name":"шаланда","shortName":"шал.","value":8192}]},{"id":5000,"name":"негабарит","shortName":"негаб.","value":18726594281984,"children":[{"id":10500,"name":"низкорамный","shortName":"рамн.","value":512},{"id":10550,"name":"низкорам.платф.","shortName":"нпл.","value":34359738368},{"id":10570,"name":"телескопический","shortName":"телскп.","value":1099511627776},{"id":10700,"name":"трал","shortName":"трал","value":536870912},{"id":20560,"name":"балковоз(негабарит)","shortName":"балк.","value":17592186044416}]},{"id":10800,"name":"автобус","shortName":"авт.","value":16384},{"id":20300,"name":"автовоз","shortName":"автв.","value":32768},{"id":20350,"name":"автовышка","shortName":"вышк.","value":65536},{"id":10100,"name":"автотранспортер","shortName":"автт.","value":131072},{"id":20500,"name":"бетоновоз","shortName":"бет.","value":262144},{"id":20550,"name":"битумовоз","shortName":"битум","value":2199023255552},{"id":20700,"name":"бензовоз","shortName":"бенз.","value":274877906944},{"id":20750,"name":"вездеход","shortName":"вздхд.","value":549755813888},{"id":10600,"name":"газовоз","shortName":"газ.","value":524288},{"id":40000,"name":"зерновоз","shortName":"зерн.","value":1048576},{"id":1280,"name":"коневоз","shortName":"кони.","value":137438953472},{"id":1300,"name":"конт.площадка","shortName":"площ.","value":2097152},{"id":1250,"name":"кормовоз","shortName":"корм.","value":4194304},{"id":10000,"name":"кран","shortName":"кран","value":8388608},{"id":10300,"name":"лесовоз","shortName":"лесв.","value":16777216},{"id":10330,"name":"ломовоз","shortName":"лом.","value":1125899906842624},{"id":1350,"name":"манипулятор","shortName":"манип","value":256},{"id":600,"name":"микроавтобус","shortName":"микр.","value":32},{"id":20200,"name":"муковоз","shortName":"мук.","value":33554432},{"id":10320,"name":"панелевоз","shortName":"панв.","value":2048},{"id":1170,"name":"пикап","shortName":"пикап","value":68719476736},{"id":20860,"name":"пухтовоз","shortName":"пухта","value":140737488355328},{"id":20850,"name":"пирамида","shortName":"пирам.","value":8796093022208},{"id":20870,"name":"рулоновоз","shortName":"рул.","value":35184372088832},{"id":10400,"name":"седельный тягач","shortName":"тягач","value":67108864},{"id":10900,"name":"скотовоз","shortName":"скот.","value":134217728},{"id":10950,"name":"стекловоз","shortName":"сткл.","value":268435456},{"id":10350,"name":"трубовоз","shortName":"труб.","value":1073741824},{"id":20100,"name":"цементовоз","shortName":"цем.","value":2147483648},{"id":10200,"name":"цистерна","shortName":"цист.","value":8589934592},{"id":20150,"name":"щеповоз","shortName":"щеп.","value":4294967296},{"id":20600,"name":"эвакуатор","shortName":"эвак.","value":17179869184},{"id":55000,"name":"грузопассажирский","shortName":"грузпас.","value":2251799813685248},{"id":55500,"name":"клюшковоз","shortName":"клюшк.","value":4503599627370496},{"id":56000,"name":"мусоровоз","shortName":"мусор.","value":9007199254740992},{"id":56500,"name":"юмбо","shortName":"юмбо","value":18014398509481984},{"id":57000,"name":"танк-контейнер 20фут","shortName":"танк-конт.20фут","value":36028797018963968},{"id":57500,"name":"танк-контейнер 40фут","shortName":"танк-конт.40фут","value":72057594037927936}]';
$TrackTypesDic = JSON_DECODE($TrackTypesDic,true);
$loadingTypes = [];
foreach ($TrackTypesDic as $type) {
    $loadingTypes[$type['value']] = $type['shortName'];
    if (isset($type['children'])) {
        foreach ($type['children'] as $child) {
            $loadingTypes[$child['value']] = $child['shortName'];
        }
    }
}

//var_dump($loadingTypes);

echo "<br><br><br><br>";

//var_dump($TrackTypesDic);
exit;
*/

include_once(__DIR__.DIRECTORY_SEPARATOR.'SimpleHTML/simple_html_dom.php');

$url = 'https://trucks.ati.su/Tables/Default.aspx';
$request_params = array(
    'EntityType' => 'Truck',
    'FromGeo' => '2_74',
    'FromGeoRadius' => '-1',
    'ToGeo' => '2_80',
    'ToGeoRadius' => '-1',
    'Weight2' => '100',
);
$get_params = http_build_query($request_params);

$url = $url.'?'.$get_params;

$headers[] = "Content-Type:application/json;charset=utf-8";
$headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0";
$headers[] = "Cookie: itemsPerPage=10;did=giR5yjP1JR2wfdf8plVKenUq8ZhUzXi1oE2037vfbkw=; sid=84c4d8306c3c47f8b66c20fc4614cd1f";

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$output = curl_exec($ch);
$status = (curl_getinfo($ch, CURLINFO_HTTP_CODE));
curl_close($ch);

echo '<hr>'.$status.'<hr>';

$output = iconv("CP1251", "UTF-8", $output);

$html = str_get_html($output);

$count = $html->find('span[id=lblRowsCount]',0)->plaintext;
echo str_replace('Количество найденных машин ','',$count);


//exit;

if($html->innertext!='' and count($html->find('table .atiTables'))){

    foreach($html->find('table .atiTables',0)->find('tr') as $tr){

        //firstRow - информация по машине
        //secondRow - контактная информация
        if (stripos($tr->class, 'firstRow')  !== false) {
            echo " 1-- ".$tr->class.'</br>';
            echo " id -- ".$tr->combinedid.'</br>';
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
                        echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                        $nn++;
                        break;

                    case'load':
                        echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                        $nn++;
                        break;

                    case'unload':

                        echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                        $nn++;
                        break;

                    case'price':

                        echo $poleType[$nn]." ".trim($td->plaintext).'</br>';
                        $nn++;
                        break;

                    default:
                        $nn++;
                        break;
                }


            }


        } elseif (stripos($tr->class, 'secondRow')  !== false) {

             if ($tr->find('table[id*=tblNote] .noteText', 0)) {

                 echo "Комментарий: " . $tr->find('table[id*=tblNote] .noteText', 0)->plaintext . '</br>';
             }

             if ($tr->find('table[id*=tblFirm]', 0)) {
                echo "Фирма: " . $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td',0)->plaintext . '</br>';
                //echo "Детали: " . $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td',1)->plaintext . '</br>';

                if($tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td',1)->plaintext){

                $match = preg_match( '#\((.+?)\)#is', $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('td',1)->plaintext, $details );

                if ($match) {
                    $details = explode(',',$details[1]);

                    if (count($details) >= 3) {


                        echo 'ФирмИД: '.str_replace('ID:','',trim($details[0])).'<br>';
                        echo 'Роль: '.$details[1].'<br>';
                        echo 'Город: '.$details[2].'<br>';


                    }

                    if ($tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('span[id*=ctlStarControl]',0)->title) {

                        $match = preg_match( '#Балл участника АТИ: (.+?). #is', $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('span[id*=ctlStarControl]',0)->title, $details );
                        if ($match) {
                            echo 'Рейтинг: '.$details[1].'<br>';

                        }
                    }

                    //echo "Детали: " . $details[1];
                }

                }

                 //echo "Рейтинг: " . $tr->find('table[id*=tblFirm]', 0)->find('table[id*=tblFirmData]', 0)->find('span[id*=ctlStarControl]',0)->title . '</br>';
             //ctlStarControl
             }


            if ($tr->find('table[id*=tblContacts]',0)->find('table[id*=ContactsData]')) {

                foreach ($tr->find('table[id*=tblContacts]',0)->find('table[id*=Contacts]') as $contact) {

                   if ($contact->find('td')) {
                       foreach ($contact->find('td') as $contTd) {

                           if ($contTd->find('input[id*=itFirmInfo]',0)) {

                               //echo 'Ид юзера '.$contTd->find('input[id*=itFirmInfo]',0)->onclick. '</br>';

                               $match = preg_match( "/https:\/\/(.+?u=\d{1,}).+,./is", $contTd->find('input[id*=itFirmInfo]',0)->onclick, $link );

                               if ($match) {
                               $url = parse_url($link[1]);
                               $get_string = $url['query'];
                               parse_str(html_entity_decode($get_string), $get_array);
                               if (isset($get_array['u'])) {echo 'Ид юзера '.$get_array['u']. '</br>';}
                               }
                           }


                           if ($contTd->find('span[class*=faxText]')) {continue;}

                           if ($contTd->find('a[class=PhoneNumberRef]')) {

                               foreach ($contTd->find('a[class=PhoneNumberRef]') as $phone) {
                                   echo "Телефон " . $phone->plaintext . '</br>';
                               }

                               $texts = explode(',',$contTd->plaintext);

                               If (isset($texts[count($texts)-1])) {

                                   $match = preg_match("/[\d]+/", $texts[count($texts)-1], $matches);
                                   if ($match) {
                                       if (strlen($matches[0]) < 2) {
                                           echo "Имя " . $texts[count($texts) - 1] . '</br>';
                                       }
                                   } else {echo "Имя " . $texts[count($texts) - 1] . '</br>';}
                               }


                           }



                       }

                   }
                }


            }

        }
    }
}

$html->clear(); // подчищаем за собой
unset($html);
