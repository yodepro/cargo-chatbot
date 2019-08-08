<?php


/** Главный управляющий класс приложения
 *
 */

class kernel {

    /** Отказ в доступе - HTTP 403*/
    public function deny()
    {
        header("HTTP/1.0 403 Forbidden");
        exit;
    }

    /** Ответ OK - HTTP 200*/
    public function ok()
    {
        header("HTTP/1.0 200 OK");
        exit;
    }

    /** Функция выполняет простой запрос по url */
    public function curl($url){
        global $tg_proxy;
        global $tg_proxy_user;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data",
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $output = curl_exec($ch);

        $status = (curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if (($status != 200) and (!empty($tg_proxy))) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $tg_proxy_user);
            curl_setopt($ch, CURLOPT_PROXY, $tg_proxy);
            $output = curl_exec($ch);

           // self::fnlog("Прокси ". serialize($output).' - '.$status);

        }
        curl_close($ch);
        return $output;
    }

    /** Функция выполняет запрос POST c данными по url */
    public function curlPost($url,$post,$headers=[]){
        global $tg_proxy;
        global $tg_proxy_user;

if (empty($headers)) {
    $headers[] = "Content-Type:multipart/form-data";
}

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $output = curl_exec($ch);

        $status = (curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if (($status != 200) and (!empty($tg_proxy))) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $tg_proxy_user);
            curl_setopt($ch, CURLOPT_PROXY, $tg_proxy);
            $output = curl_exec($ch);

            // self::fnlog("Прокси ". serialize($output).' - '.$status);

        }
        curl_close($ch);

        if ($status != 200) return $status;

        return $output;
    }

    /** Функция выполняет запрос GET c заголовками по url + поддержка Cookies */
    public function curlGet($url,$headers=[]){


        if (empty($headers)) {
            $headers[] = "Content-Type:multipart/form-data";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_COOKIEJAR, DOC_ROOT.'/cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, DOC_ROOT.'/cookie.txt');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $output = curl_exec($ch);

        $status = (curl_getinfo($ch, CURLINFO_HTTP_CODE));


        curl_close($ch);

        if ($status != 200) return $status;

        return $output;
    }

    /**  Функция логирования */
    public function fnlog($log){
        global $log_file;
        $fp = fopen($log_file, 'a');
        $date = date('G:i:s d.m.Y');

        if (is_array($log)) {

            $log = "МАССИВ:  ".JSON_ENCODE($log,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        }

        fwrite($fp, $date.' '.$log.PHP_EOL);
        fclose($fp);
    }

    /** Функция для получения заголовков, если не работает стандартная (бывает в ряде случаев) */
    private function  mygetallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /** Получаем данные юзера */
    public function get_user($userdata){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");



        $result = $mysqli->query("
SELECT * FROM `users` WHERE `userId` = '".$userdata['userId']."' and `messagerType` = '".$userdata['messagerType']."' LIMIT 1
");

        //Если юзер уже был у нас
        if (($result) and ($result->num_rows > 0)) {
            $user = $result->fetch_assoc();
            $Current ='';
            if (($user['currentStep'] == '0.0') or ($user['currentStep'] == '0.0')){
                $user['currentStep'] = '0.1'; $Current=", `currentStep`='0.1', `tmpData` = '' ";
            }

            $mysqli->query("
            UPDATE `users` SET `firstName`='".$userdata['firstName']."', `lastName`='".$userdata['lastName']."', `lastVisit`=NOW()".$Current." WHERE  `userId` = '".$userdata['userId']."' and `messagerType` = '".$userdata['messagerType']."'
            ");
            if ($mysqli->insert_id) {$user['lastVisit'] = date('Y-m-d H:m:i');}

            mysqli_close($mysqli);
            return $user;
        } else { //Если новый юзер
            $mysqli->query("
        INSERT INTO `users`(`userId`, `messagerType`, `lastVisit`, `firstName`, `lastName`, `balance`, `currentStep`, `tmpData`, `isAdmin`, `createDate`) 
        VALUES (        
        '".$userdata['userId']."',
        '".$userdata['messagerType']."',
        NOW(),
        '".$userdata['firstName']."',
        '".$userdata['lastName']."',
        0,
        '0.0',
        '',
        0,
        NOW()
        )                
            ");

            if ($mysqli->insert_id > 0) {
                return [
                    'id'=> $mysqli->insert_id,
                    'userId'=>$userdata['userId'],
                    'messagerType'=>$userdata['messagerType'],
                    'lastVisit'=>date('Y-m-d H:m:i'),
                    'firstName'=>$userdata['firstName'],
                    'lastName'=>$userdata['lastName'],
                    'balance'=>0,
                    'currentStep'=>'0.0',
                    'tmpData'=>'',
                    'isAdmin'=>0,
                    'createDate'=>date('Y-m-d H:m:i'),
                    'newUser'=>true,
                     ];
                mysqli_close($mysqli);

            } else { mysqli_close($mysqli);self::fnlog('Не удалось создать запись пользователя');}

        }



    }

    /** Получаем текущий шаг юзера*/
    public function get_currentStep($step){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $result = $mysqli->query("
SELECT * FROM `dialogTree` WHERE `step` = '".$step."' LIMIT 1
");

        //Если юзер уже был у нас
        if (($result) and ($result->num_rows > 0)) {
            $step = $result->fetch_assoc();
            mysqli_close($mysqli);
            return $step;
        } else {
            self::fnlog('Не удалось получить настройки шага '.$step);
            exit;
        }

    }

    /** Проверяем введенные данные на соответсвие */
    public function verifyInput($text,$params,$nextStep){

        $text = trim($text);
        $params = JSON_DECODE($params,true);
        $type = '';$input_value ='';
        //Получаем тип поля и исследуемый параметр
        foreach ($params as $types => $key) {
            $type = $types;
            $input_value = $key;
            break;
        }

        //self::fnlog($type.' '.$input_value.' '.$text);

        if ((empty($type)) or (empty($input_value))) {return false;}

        switch ($type){
            case'string':
                //self::fnlog('Тип строка');
                $regex = "/^[A-Za-zА-Яа-яЁе -]+$/u";
                $status = (preg_match($regex, $text) ? TRUE : FALSE);
                if ($status === true) {
                return [
                    'status'=>$status,
                    'input_value'=>$input_value,
                    'text'=>$text,
                    'step'=>$nextStep,
                ];
                } else {
                    return [
                        'status'=>$status,
                        'message'=>'Проверьте правильность введенных данных!',
                    ];
                }
                break;

            case'numeric':
                $regex = "/^[0-9]+$/u";
                $status = (preg_match($regex, $text) ? TRUE : FALSE);
                if ($text == '0') {$status = false;}
                //Если слишком большое число
                if ((mb_strlen($text)) > 5) {
                    return [
                        'status'=>false,
                        'message'=>'Слишком большое число, введите не более 5 знаков!',
                    ];

                }
                if ($status === true) {
                    return [
                        'status'=>$status,
                        'input_value'=>$input_value,
                        'text'=>$text,
                        'step'=>$nextStep,
                    ];

                } else {
                    return [
                        'status'=>$status,
                        'message'=>'Проверьте правильность введенных данных!',
                    ];
                }
                break;

            case'phone':

                if (mb_strlen($text) < 6) {
                    return [
                        'status'=>false,
                        'message'=>'Проверьте правильность введенных данных!',
                    ];
                }

                $regex = "/^[0-9+()-]+$/u";
                $status = (preg_match($regex, $text) ? TRUE : FALSE);
                if ($text == '0') {$status = false;}
                if ($status === true) {
                    return [
                        'status'=>$status,
                        'input_value'=>$input_value,
                        'text'=>$text,
                        'step'=>$nextStep,
                    ];

                } else {
                    return [
                        'status'=>$status,
                        'message'=>'Проверьте правильность введенных данных!',
                    ];
                }
                break;

            default:
                return false;
                break;
        }

        return false;

    }

    /** Обновляем текущий шаг юзера */
    public function stepUpdate($step,$userId,$typeMessager){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("
UPDATE `users` SET `currentStep`='".$step."' WHERE `userId` = '".$userId."' and `messagerType` = '".$typeMessager."'
");
        mysqli_close($mysqli);


}

    /** Обновляем текущие инпут-данные юзера */
    public function updateTmpData($userId,$typeMessager,$inputValue,$text,$reload=false){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $result = $mysqli->query("
        SELECT `tmpData` FROM `users` WHERE `userId` = '".$userId."' and `messagerType` = '".$typeMessager."' LIMIT 1       
        ");

        if (($result) and ($result->num_rows > 0)) {
            $tmpData = $result->fetch_assoc();

            if (!empty($tmpData['tmpData'])) {

                if ((empty($inputValue)) and (empty($text))) {

                    $data = '';

                } elseif ($reload == true) {

                    $data = $text;

                } else {

                        $data = json_decode($tmpData['tmpData'], true);
                        $data['' . $inputValue . ''] = $text;
                        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                }

            } else {

                if ((empty($inputValue)) and (empty($text))) {

                    $data ='';

                } elseif ($reload == true) {

                    $data = $text;

                } else {



                        $data['' . $inputValue . ''] = $text;
                        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);




                }
            }

            $mysqli->query("
UPDATE `users` SET `tmpData`='".$mysqli->real_escape_string($data)."' WHERE `userId` = '".$userId."' and `messagerType` = '".$typeMessager."'
");
            mysqli_close($mysqli);


        } else {
            self::fnlog('Не удалось получить текущие параметры пользователя '.$userId.' '.$typeMessager);
            exit;
        }

    }

    /** Производим поиск по БД */
    public function get_search_data($data,$value,$userData=[]){

        if (empty($value)) {return false;}

        $limit = PAGE + 1;
        $offset = 0;



        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

if (!empty($data)) {
        $data = JSON_DECODE($data,true);
        if (!isset($data['radius'])) {$data['radius'] = RADIUS;}
        if (isset($data['page'])) {$offset = PAGE * $data['page'];}
        }

        switch ($value){

            case'cargo':

                $ati    = new ati;
                //$data   = JSON_DECODE($userData['tmpData'],true);
                $geoId1 = $ati ->getGeoId($data['starting']);
                $geoId2 = $ati ->getGeoId($data['destination']);

                $userTmp = JSON_DECODE($userData['tmpData'],true);
                if (isset($userTmp['page'])) {$page = $userTmp['page'] + 1;} else {$page = 1;}

               return $ati->getSearchCargo($geoId1,$geoId2,$data['tonnage'],$data['radius'],$page);

                break;

            case 'auto':


                $ati    = new ati;
                //$data   = JSON_DECODE($userData['tmpData'],true);
                $geoId1 = $ati ->getGeoId($data['starting']);
                $geoId2 = $ati ->getGeoId($data['destination']);

                $userTmp = JSON_DECODE($userData['tmpData'],true);
                if (isset($userTmp['page'])) {$page = $userTmp['page'] + 1;} else {$page = 1;}

                //self::fnlog($ati->getSearchTruck($geoId1,$geoId2,$data['tonnage'],$data['radius'],$page));

                return $ati->getSearchTruck($geoId1,$geoId2,$data['tonnage'],$data['radius'],$page);

                //exit;

                $result = $mysqli->query("
                
                SELECT * FROM `AutoTmp` WHERE `start` LIKE '%".$data['starting']."%' and `end` LIKE '%".$data['destination']."%' and `tonnage` <= '".$data['tonnage']."' and `radius` <= '".$data['radius']."' ORDER BY `rating` DESC LIMIT ".$offset.",".$limit." 

");
                //self::fnlog("SELECT * FROM `AutoTmp` WHERE `start` LIKE '%".$data['starting']."%' and `end` LIKE '%".$data['destination']."%' and `tonnage` <= '".$data['tonnage']."' and `radius` <= '".$data['radius']."' LIMIT 10");


                if (($result) and ($result->num_rows > 0)) {

                    $result_arr = [];

                    while ($auto = $result->fetch_assoc()) {
                        $result_arr[] = $auto;
                    }

                    return  $result_arr;
                } else {return [];}

                break;

            case 'history':

                $result = $mysqli->query("                
                SELECT * FROM `viewHistory` WHERE `userId` = ".$userData['id']." ORDER BY `viewDate` DESC LIMIT ".$offset.",".$limit."
");

                if (($result) and ($result->num_rows > 0)) {

                    $result_arr = [];

                    while ($history = $result->fetch_assoc()) {
                        $result_arr[] = $history;
                    }

                    return $result_arr;

                } else return [];

                break;

            default:
                return false;
                break;




        }

}

    /** Получаем количество данных в БД */
    public function get_count_data($data,$value,$userData=[]){

        if (empty($value)) {return false;}

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");


        $data = JSON_DECODE($data,true);
        if (!isset($data['radius'])) {$data['radius'] = RADIUS;}


        switch ($value){

            case'cargo':
                $ati = new ati;
                //$data = JSON_DECODE($userData['tmpData'],true);
                $geoId1 = $ati ->getGeoId($data['starting']);
                $geoId2 = $ati ->getGeoId($data['destination']);
                if ((empty($geoId1)) or (empty($geoId2))) {return false;}
                $count = $ati->getSearchCargo($geoId1,$geoId2,$data['tonnage'],$data['radius'],1,true);
                return $count;
/*
                $result = $mysqli->query("
                
                SELECT count(*) as count FROM `CargoTmp` WHERE `start` LIKE '%".$data['starting']."%' and `end` LIKE '%".$data['destination']."%' and `tonnage` <= '".$data['tonnage']."' and `radius` <= '".$data['radius']."'

");
                //self::fnlog("SELECT count(*) as count FROM `CargoTmp` WHERE `start` LIKE '%".$data['starting']."%' and `end` LIKE '%".$data['destination']."%' and `radius` >= '".$data['radius']."'");
                //Если юзер уже был у нас
                if ($result->num_rows > 0) {
                    $count = $result->fetch_assoc();
                    //self::fnlog('Количество '.intval($count['count']));
                    return intval($count['count']);

                } else {return 0;}
*/

                break;

            case'auto':


                $ati = new ati;
                //$data = JSON_DECODE($userData['tmpData'],true);
                $geoId1 = $ati ->getGeoId($data['starting']);
                $geoId2 = $ati ->getGeoId($data['destination']);
                if ((empty($geoId1)) or (empty($geoId2))) {return false;}
                $count = $ati->getSearchTruck($geoId1,$geoId2,$data['tonnage'],$data['radius'],1,true);
                return $count;



                $result = $mysqli->query("
                
                SELECT count(*) as count FROM `AutoTmp` WHERE `start` LIKE '%".$data['starting']."%' and `end` LIKE '%".$data['destination']."%' and `tonnage` <= '".$data['tonnage']."' and `radius` <= '".$data['radius']."'

");

                //Если юзер уже был у нас
                if (($result) and ($result->num_rows > 0)) {
                    $count = $result->fetch_assoc();
                    //self::fnlog('Количество '.intval($count['count']));
                    return intval($count['count']);
                } else {return 0;}

                break;

            case 'history':

                $result = $mysqli->query("
                
                SELECT count(*) as count FROM `viewHistory` WHERE `userId` = '".$userData['id']."'

");

                //Если юзер уже был у нас
                if (($result) and ($result->num_rows > 0)) {
                    $count = $result->fetch_assoc();
                    return intval($count['count']);
                } else {return 0;}

                break;

            default:
                return false;
                break;




        }

    }

    /** Получаем значение в БД */
    public function get_value_data($data,$value,$userData=''){

        if (empty($value)) {return false;}

        switch ($value){

            case'balance':

                if ($userData['balance'] < PRICE) {return false;}  else {return true;}
                break;

            case'phone':

                $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
                if ($mysqli->connect_errno) {
                    self::fnlog('Не удалось подключиться к серверу MySQL');
                    exit;
                }
                $mysqli->set_charset("utf8");

                $result = $mysqli->query("
SELECT `phone` FROM `users` WHERE `userId` = '".$userData['userId']."' and `messagerType` = '".$userData['messagerType']."' LIMIT 1
");
                if (($result) and ($result->num_rows > 0)) {
                    $user = $result->fetch_assoc();
                    return $user['phone'];
                } else {return '';}

                return false;
                break;

            default:
                return false;
                break;




        }

    }

    /** Сохраняем телефон пользователя */
    public function saveUserTel($userId,$typeMessager,$phone){

        if ((empty($userId)) or (empty($typeMessager)) or (empty($phone))) {return false;}

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("
UPDATE `users` SET `phone`='".$phone."' WHERE `userId` = '".$userId."' and `messagerType` = '".$typeMessager."'
");

        //self::fnlog('insert_id '.$mysqli->affected_rows);
        if ($mysqli->affected_rows > 0) {return true;}

        mysqli_close($mysqli);
        return false;


    }

    /** Получаем карточку клиента для показа */
    public function getContactData($id){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
                if ($mysqli->connect_errno) {
                    self::fnlog('Не удалось подключиться к серверу MySQL');
                    exit;
                }
$mysqli->set_charset("utf8");

        $id = implode(',',$id);

$result = $mysqli->query("
SELECT * FROM `Contacts` WHERE `id` IN (".$id.")
");

//self::fnlog("SELECT * FROM `Contacts` WHERE `id` IN (".$id.")");

        if (($result) and ($result->num_rows > 0)) {

    $result_arr = [];

    while ($contacts = $result->fetch_assoc()) {
        $result_arr[] = $contacts;
    }




    return $result_arr;
} else {self::fnlog('Не распознан тип карточки клиента');exit;}



    }

    /** Списываем сумму оплаты просмотра со счета пользователя */
    public function chargeOff($userId,$sum) {


        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");


        $mysqli->query("
UPDATE `users` SET `balance`=(`balance` - ".intval($sum).") WHERE `id` = '".$userId."'
");

        mysqli_close($mysqli);

    }

    /** Записываем просмотр контакта в лог просмотров */
    public function saveToViewsHistory($userId,$contactId,$contactData,$listId = ''){

        $contactId = explode(',',$contactId);
        $contactId = $contactId[0];

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("
INSERT INTO `viewHistory`(`contactID`, `userId`,`listId`,`contact`, `viewDate`) VALUES (
'".$contactId."',
'".$userId."',
'".$listId."',
'".$mysqli->real_escape_string($contactData)."',
NOW()
)
");




        mysqli_close($mysqli);


    }

    /** Сохраняем контакт в БД */
    public function  saveContact($contactData){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("
INSERT INTO `Contacts` (`gId`, `CompanyName`, `profile`, `city`,`contactName`,`phones`, `UpdateDate`,`rand`) 
VALUES (
'".$contactData['id']."',
'".$contactData['firmName']."',
'".$contactData['profile']."',
'".$contactData['city']."',
'".$contactData['nameContact']."',
'".$contactData['phones']."',
NOW(),
".rand(0,10000)."
)

ON DUPLICATE KEY UPDATE

`CompanyName` = '".$contactData['firmName']."',
`profile` = '".$contactData['profile']."',
`city` = '".$contactData['city']."',
`contactName` = '".$contactData['nameContact']."',
`phones` = '".$contactData['phones']."',
`UpdateDate` = NOW(),
`rand` = ".rand(0,10000)."


");
        //self::fnlog('id '.$mysqli->insert_id);
        return $mysqli->insert_id;

        mysqli_close($mysqli);


    }

    /** Кешируем ID записи источника данных в БД */
    public function  saveIDtoCash($id){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("
INSERT INTO `id_cash` (`listId`, `update_time`,`rand`) 
VALUES (
'".$id."',
NOW(),
".rand(0,10000)."
)

ON DUPLICATE KEY UPDATE

`update_time` = NOW(),
`rand` = ".rand(0,10000)."



");
        return $mysqli->insert_id;

        mysqli_close($mysqli);


    }

    /** Кешируем ID записи источника данных в БД */
    public function  VerifyIDCash($UserId,$listId){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $result=$mysqli->query("

SELECT `viewDate` FROM `viewHistory` WHERE `userId` = '".$UserId."' and `listId` = '".$listId."' LIMIT 1

");

        if (($result) and ($result->num_rows > 0)) {
            $date = $result->fetch_assoc();
            return $date['viewDate'];

        } else {return false;}


        mysqli_close($mysqli);


    }

    /** Передаем данные управляющему скрипту с коротким таймаутом */
    public function RetransferData($url,$post){

        if (empty($headers)) {
            $headers[] = "Content-Type:multipart/form-data";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }

    /** Кешируем сообщения по ид источника данных в БД */
    public function  saveCashMessage($id,$message){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("

UPDATE `id_cash` SET `message`='".$mysqli->real_escape_string($message)."', `update_time`=NOW() WHERE `id` = '".$id."'


");
        //return $mysqli->insert_id;


        mysqli_close($mysqli);


    }

    /** получаем сообщение по ид из кэша БД */
    public function  getCashMessage($id){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $result=$mysqli->query("

SELECT `message` FROM `id_cash` WHERE `id` ='".$id."' limit 1


");

        if (($result) and ($result->num_rows > 0)) {
            $message = $result->fetch_assoc();
            return $message['message'];

        } else {self::fnlog('Не найден кэш сообщения');exit;}


        mysqli_close($mysqli);


    }

    /** Обрезка строки по словам по длине */
    public function CutStr($str, $length, $postfix='...', $encoding='UTF-8')
    {

        //return "строка1"."<br>"."строка2"."<br>"."строка3"."<br>"."строка4"."<br>"."строка5"."<br>"."строка6"."<br>"."строка7";


        if (mb_strlen($str, $encoding) <= $length) {
            return $str;
        }

        $tmp = mb_substr($str, 0, $length, $encoding);
        return mb_substr($tmp, 0, mb_strripos($tmp, ' ', 0, $encoding), $encoding) . $postfix;
    }

    /** Создаем счет на оплату */
    public function createPayOrder($userID,$orderSum,$orderDesc,$orderCurrency){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("

INSERT INTO `payOrders`(`userId`, `orderSum`, `orderCurrency`, `orderDesc`, `orderStatus`,`updateDate`) VALUES (
'".$userID."',
'".$orderSum."',
'".$orderCurrency."',
'".$mysqli->real_escape_string($orderDesc)."',
'created',
NOW()
)
");

        if ($mysqli->insert_id) {return $mysqli->insert_id;} else {return false;}

        mysqli_close($mysqli);
    }

    /** Получаем счет на оплату */
    public function getPayorder($orderId){
        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $result = $mysqli->query("
SELECT * FROM `payOrders` WHERE `id` = '".$orderId."' limit 1
");

        if (($result) and ($result->num_rows > 0)) {
            $order = $result->fetch_assoc();
            return $order;
        } else {self::fnlog('Не найден счет на оплату '.$orderId);exit;}

        mysqli_close($mysqli);
    }

    /** Получаем ссылку на UnitPay для оплаты */
    public function getUnitPayUrl($orderId){

        require_once(DOC_ROOT.'unitpay'.DIRECTORY_SEPARATOR.'UnitPay.php');

        $projectId  = UP_PROJECT;
        $secretKey  = UP_SECRET;
        $publicId   = UP_ID;


        $unitPay = new UnitPay($secretKey);

        $order=self::getPayorder($orderId);

        $redirectUrl = $unitPay->form(
            $publicId,
            $order['orderSum'],
            $orderId,
            $order['orderDesc'],
            $order['orderCurrency']
        );

        return $redirectUrl;


    }

    /** Обновляем статус счета на оплату и баланс пользователя */
    public function updateOrder($orderId, $status, $params='',$userId='', $sum = 0,$balance = false) {

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $mysqli->query("
UPDATE `payOrders` SET `orderStatus`='".$status."',`updateDate`=NOW(),`orderInfo`='".$mysqli->real_escape_string($params)."' WHERE `id` = '".$orderId."'
");

        if (($mysqli->affected_rows) > 0) {

            if ($balance === false) {return true;}

            $mysqli->query("
UPDATE `users` SET `balance`= (`balance` + ".$sum.")  WHERE `id` = '".$userId."' 
");

            if (($mysqli->affected_rows) > 0) {
                //Оповещаем пользователя
                self::balanceMessage($userId,$sum,$params);
                return true;
                } else {return false;}


        } else {return false;}

        mysqli_close($mysqli);



    }

    /** Оповещаем пользователя о пополнении счета */
    public function balanceMessage($userId, $orderSum, $params){

        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $result = $mysqli->query("
SELECT * FROM `users` WHERE `id` = '".$userId."' limit 1
");

        if (($result) and ($result->num_rows > 0)) {
            $userData = $result->fetch_assoc();
        } else {return false;}


        $params = JSON_DECODE($params,true);
        $message = "Произведено пополнение баланса на сумму ".$orderSum.$params['orderCurrency'].'.'.PHP_EOL.
            'Ваш баланс: {balance} руб.'.PHP_EOL.
            'Спасибо, что выбрали нас!';

        $class = new $userData['messagerType']();
        $button = '{"В начало":"0.1"}';
        $class->sendMessage($userData['userId'],$message,'',$userData,$button);

    }

    /** Оповещает администратора об ошибках получения данных
     *  Учетные данные абонентов берутся из БД
     */
    public function alertMessage($message){

        date_default_timezone_set('Europe/Moscow');
        $hour = intval(date('G'));
        if ((($hour >= 0) and ($hour < 6)) or ($hour > 23)) {
            return ''; //Блокируем сообщения ночью
        }
            //Запишем событие в лог
            self::fnlog($message);



        $mysqli = new mysqli(A_ADDRESS,A_LOGIN, A_PASSWORD, A_NAME);
        if ($mysqli->connect_errno) {
            self::fnlog('Не удалось подключиться к серверу MySQL');
            exit;
        }
        $mysqli->set_charset("utf8");

        $result = $mysqli->query("
SELECT * FROM `users` WHERE `isAdmin` = 1 and `alertDate` < (NOW() - INTERVAL 3 HOUR)
");

        if (($result) and ($result->num_rows > 0)) {
            while ($userData = $result->fetch_assoc()) {
                $class = new $userData['messagerType']();
                $class->sendMessage($userData['userId'],$message,'','','');
                $mysqli->query("UPDATE `users` SET `alertDate` = NOW() WHERE `id` = '".$userData['id']."'");
            }
        } else {return false;}

    }

}