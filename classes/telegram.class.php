<?php

/** Класс для обработки функционала бота Telegram
 *
 * https://api.telegram.org/bot672227440:AAGuXZjKbXsfBkSOPUUECr2I64orZgRO9Rg/setWebhook?url=https://cargobot.myode.pro/672227440:AAGuXZjKbXsfBkSOPUUECr2I64orZgRO9Rg
 *
 */


class telegram {

    /** Обработка входящих сообщений */
    public function incoming()
    {
        global $kernel;
        //если нет входящих параметров - отказ
        if (!isset($_REQUEST)) {
            $kernel->deny();
        }




        $data2 = JSON_ENCODE(json_decode(file_get_contents('php://input',true)),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents('./telegram_data_log.txt', $data2);

        $data = json_decode(file_get_contents('php://input')); //читаем данные от Telegram
        if (empty($data)) {$kernel->deny();} //если пустые данные или нет сообщения - отказ
        self::parseMessage($data);
    }

    /**  Разбираем сообщение для отправки в CRM */
    private function parseMessage($data='')
    {
        global $kernel;
        global $telegram_token;

        if (empty($data)) $kernel->deny(); //если пустые данные - отказ
        //$kernel->fnlog('Входящее от Telegram '.json_encode($data,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

$nn = 0;
$contact_message = false;
$autoreread = false;


        if (isset($data->message))  { //если это сообщение


            If ((isset($data->message->text)) and (($data->message->text == '/start') or ($data->message->text == 'В начало'))) {$kernel->stepUpdate('0.0',$data->message->from->id,'telegram');}
            If ((isset($data->message->text)) and ($data->message->text == 'Техподдержка')) {$kernel->stepUpdate('3.0',$data->message->from->id,'telegram');}


            reread: //Автоматически перечитываем параметры по команде ниже по коду
$nn++;
            if($nn > 10) {

                self::sendMessage($data->message->from->id, 'Команда не распознана! Попробуйте сначала.', $telegram_token);
                exit;
            } //контроль зацикливания - выбивает после 10 возвратов

            if (isset($data->message)) {

                if (!isset($data->message->text)) {$text='';} else {$text=$data->message->text;}

                $userData = [
                    'userId' => $data->message->from->id,
                    'messagerType' => 'telegram',
                    'firstName' => 'Гость',
                    'lastName' =>  '',
                    'text' => $text,
                ];

               if (isset($data->message->from->first_name)) {$userData['firstName'] = $data->message->from->first_name;}
               if (isset($data->message->from->last_name)) {$userData['lastName'] = $data->message->from->last_name;}

            } elseif (isset($data->callback_query)) {
                $userData = [
                    'userId' => $data->callback_query->from->id,
                    'messagerType' => 'telegram',
                    'firstName' => 'Гость',
                    'lastName' => '',
                    'text' => '',
                ];
                if (isset($data->callback_query->from->first_name)) {$userData['firstName'] = $data->callback_query->from->first_name;}
                if (isset($data->callback_query->from->last_name)) {$userData['lastName'] = $data->callback_query->from->last_name;}
            }


            //Получаем данные юзера
            $user = $kernel->get_user($userData);


            //Получаем данные текущего шага юзера
            $currentStep = $kernel->get_currentStep($user['currentStep']);

            //Если с начала - чистим переменные
            if (($currentStep['step'] == '0.1') or ($currentStep['step'] == '0.0')) {
                //$kernel->fnlog('Чистим временные данные');
                $kernel->updateTmpData($userData['userId'],'telegram','','');
            }


            //Если передан контакт
            if ((isset($data->message->contact)) and ($contact_message == false)){

                $phoneStatus = $kernel->saveUserTel($data->message->from->id,'telegram',$data->message->contact->phone_number);

                if ($phoneStatus == true) {
                    self::sendMessage($data->message->from->id, "Номер телефона успешно сохранен!", $telegram_token,$user,'','',true);
                    $contact_message = true;

                    if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {
                        $kernel->stepUpdate($currentStep['nextStep'],$user['userId'],'telegram');

                        $autoreread = true;goto reread;
                    } else {
                        $kernel->stepUpdate('0.0',$user['userId'],'telegram');
                        $autoreread = true;goto reread;
                    }


                } else {
                    self::sendMessage($data->message->from->id, "Не удалось сохранить номер телефона!", $telegram_token,$user);                                        exit;
                }

            }

            //Устанавливаем текст из шага в сообщение, если есть
            if (!empty($currentStep['text'])) {
                $message = $currentStep['text'];
            } else {$message = '';}

            if (($currentStep['typeStep'] != 'input') and (!empty($userData['text']))
                and ($userData['text'] != '/start')
                and ($userData['text'] != 'В начало')
                and ($userData['text'] != 'Техподдержка')
            and ($autoreread != true)
            ) {
                self::sendMessage($data->message->from->id, 'Извините, команда не распознана!', $telegram_token,$user);
                exit;
            }

            //проброс шага, если это не контакт и есть флаг проброса
            $button_data = json_decode($user['tmpData'],true);
            if (($currentStep['typeStep'] != 'contact') and (isset($button_data['repeat'])) and ($button_data['repeat'] === true)) {
                $kernel->stepUpdate($currentStep['nextStep'],$user['userId'],'telegram');
                $autoreread = true;goto reread;
            }

            //Смотрим тип текущего шага
            switch ($currentStep['typeStep']){

                case 'buttons':
                    self::sendMessage($userData['userId'], $message, $telegram_token,$user,$currentStep['stepValues']);
                    exit;
                    break;

                case 'input':

                    //$kernel->fnlog('Проверяем параметры ввода');

                    if (empty($userData['text'])) {
                        //Для кнопки телефона
                        if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {$type = 'phone';} else {$type='';}
                        self::sendMessage($userData['userId'], $currentStep['text'], $telegram_token,$user,'',$type);
                        exit;
                    }

                    $messageParam = $kernel->verifyInput($userData['text'],$currentStep['stepValues'],$currentStep['nextStep']);

                    if ($messageParam) {
                        //$kernel->fnlog(JSON_ENCODE($messageParam, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                        //Если новый поиск - чистим данные поиска
                        if (($messageParam['status'] === true) and ($messageParam['input_value'] == 'starting')) {
                            $kernel->updateTmpData($userData['userId'],'telegram','','');
                        }

                        if (($messageParam['status'] === true) and ($messageParam['input_value'] == 'radius')) {
                            if ((isset($user['tmpData'])) and (!empty($user['tmpData']))) {
                              $tmpData = JSON_DECODE($user['tmpData'],true);
                              if (isset($tmpData['radius'])) {
                                  if (intval($tmpData['radius']) > intval($messageParam['text'])) {
                                      $messageParam['status'] = false; $messageParam['message'] = "Проверьте правильность введенных данных!";
                                  }
                              } else {
                                  if (intval(RADIUS) > intval($messageParam['text'])) {
                                      $messageParam['status'] = false; $messageParam['message'] = "Проверьте правильность введенных данных!";
                                  }
                              }

                            }
                        }

                        if ($messageParam['status'] === true) {

                            if ($messageParam['input_value'] == 'phone') {

                            $phoneStatus = $kernel->saveUserTel($data->message->from->id, 'telegram', $messageParam['text']);

                                if ($phoneStatus == true) {
                                    self::sendMessage($data->message->from->id, "Номер телефона успешно сохранен!", $telegram_token,$user,'','',true);
                                    $contact_message = true;

                                    if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {
                                        $kernel->stepUpdate($currentStep['nextStep'],$user['userId'],'telegram');
                                        $autoreread = true;goto reread;
                                    } else {
                                        $kernel->stepUpdate('0.0',$user['userId'],'telegram');
                                        $autoreread = true;goto reread;
                                    }


                                } else {
                                    self::sendMessage($data->message->from->id, "Не удалось сохранить номер телефона!", $telegram_token,$user);                                        exit;
                                }

                            }

                            $kernel->updateTmpData($userData['userId'],'telegram',$messageParam['input_value'],$messageParam['text']);
                            $currentStep = $kernel->get_currentStep($messageParam['step']);
                            //$kernel->fnlog(JSON_ENCODE($currentStep, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                            if (!empty($currentStep['text'])) {
                                $message = $currentStep['text'];
                            } else {$message = '';}
                            $kernel->stepUpdate($currentStep['step'],$userData['userId'],'telegram');

                            //Автопереход шага
                            if ($currentStep['typeStep'] != 'input') {$autoreread = true;goto reread;}

                        }else{
                            $message = $messageParam['message'].PHP_EOL.$message;

                            if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {
                                $type = 'phone';
                                self::sendMessage($userData['userId'], $message, $telegram_token, $user, '', $type);
                                exit;
                            }
                        }

                    self::sendMessage($userData['userId'], $message, $telegram_token,$user);
                    } else {$kernel->fnlog('Не удалось распознать параметры ввода');}
                    exit;
                    break;

                    //Если требуется запрос в БД
                case 'query':

                    $type = '';$value ='';
                    //Получаем тип запроса и исследуемый параметр
                    foreach (json_decode($currentStep['stepValues'],true) as $types => $key) {
                        $type = $types;
                        $value = $key;
                        break;
                    }

                    //$kernel->fnlog('query '.$type.' '.$value);

                    switch ($type){
                        //Запрос на поиск массива значений
                        case'search':

                            $search_data = $kernel->get_search_data($user['tmpData'],$value);

                            //Если значений в массиве больше 0
                            if (count($search_data) > 0) {

                                $kernel->stepUpdate($currentStep['step'].'.1',$data->message->from->id,'telegram');
                                $currentStep = $kernel->get_currentStep($currentStep['step']);



                            }elseif (count($search_data) == 0){

                                $kernel->stepUpdate($currentStep['step'].'.0',$data->message->from->id,'telegram');
                                $currentStep = $kernel->get_currentStep($currentStep['step']);

                            }else {
                                $kernel->fnlog('Не удалось произвести поиск '.$value);
                            exit;
                            }

                        //Запрос на количество найденных значений (предварительный)
                        case 'count':

                            $count_data = $kernel->get_count_data($user['tmpData'],$value,$user);

                            if ($count_data === false) {
                                $message = 'Не удалось определить город, пожалуйста напишите город в правильном формате.';
                                $button = '{"Попробовать еще раз":"'.$currentStep['nextStep'].'","В начало":"0.1"}';
                                self::sendMessage($userData['userId'], $message, $telegram_token,$user,$button);

                                $kernel->stepUpdate('0.1',$userData['userId'],'telegram');
                                exit;
                            }

                            if ($count_data > 0) {

                                if (!empty($currentStep['text'])) {
                                self::sendMessage($userData['userId'], str_replace('{count}',$count_data,$currentStep['text']), $telegram_token,$user);
                                }

                                //$kernel->fnlog('Обновляем шаг '.$currentStep['step'].'.1'.' '.$userData['userId']);

                                $kernel->stepUpdate($currentStep['step'].'.1',$userData['userId'],'telegram');
                                $autoreread = true;goto reread;
                                //$currentStep = $kernel->get_currentStep($currentStep['step']);


                            }elseif ($count_data == 0){

                                $kernel->stepUpdate($currentStep['step'].'.0',$userData['userId'],'telegram');
                                $autoreread = true;goto reread;
                                //$currentStep = $kernel->get_currentStep($currentStep['step']);

                            } else {
                                $kernel->fnlog('Не удалось произвести подсчет количества '.$value);
                                exit;
                            }

                            break;

                        //Запрос на получение единичного значения (по типу значения)
                        case 'value':

                            $value_data = $kernel->get_value_data($user['tmpData'],$value,$user);

                            //если тип значения - телефон
                            if ($value == 'phone') {
                                //$kernel->fnlog('Проверяем телефон пользователя');
                               if  (!empty($value_data)) {
                                   //$kernel->fnlog('Есть телефон пользователя');
                                   $kernel->stepUpdate($currentStep['step'].'.1',$user['userId'],'telegram');
                                   $autoreread = true;goto reread;
                               } elseif (empty($value_data)) {
                                   //$kernel->fnlog('Нет телефона пользователя');
                                   $kernel->stepUpdate($currentStep['step'].'.0',$user['userId'],'telegram');
                                   $autoreread = true;goto reread;
                               }  elseif  ($value_data == false) {
                                  $kernel->fnlog('Не удалось проверить телефон пользователя');
                               }
                            }

                            //Если проверка данных на тип - положительная
                            if ($value_data == true) { //посылаем по дереву 1
                                $kernel->stepUpdate($currentStep['step'].'.1',$userData['userId'],'telegram');
                                goto reread;
                            } elseif ($value_data == false) { //посылаем по дереву 0
                                $kernel->stepUpdate($currentStep['step'].'.0',$userData['userId'],'telegram');
                                goto reread;
                            }

                            break;




                    }

                    self::sendMessage($userData['userId'], "Команда не распознана, попробуйте сначала!", $telegram_token,$user);
                    exit;

                    break;

                    //Если требуется список значений
                case'list':

                    $type = '';$value ='';
                    //Получаем тип листинга и исследуемый параметр
                    foreach (json_decode($currentStep['stepValues'],true) as $types => $key) {
                        $type = $types;
                        $value = $key;
                        break;
                    }


                    $listing = $kernel->get_search_data($user['tmpData'], $value, $user);

                    //получаем общее количество
                    $total = $listing['totalCount'];
                    unset($listing['totalCount']);

                    //кол-во в массиве
                    $count = count($listing);


                    $userTmp = JSON_DECODE($user['tmpData'],true);
                    if (isset($userTmp['page'])) {$page = $userTmp['page'] + 1;} else {$page = 1;}

                    //если тип списка - история покупок контактов
                    if ($value == 'history'){
                        $nn = 0;
                        $num = 1;
                        if ($page > 1) {$num = (($page - 1) * PAGE) + 1;}
                        foreach ($listing as $item) {

                            self::sendMessage($userData['userId'],  "*".$num.") Просмотренный контакт:*"
                                .PHP_EOL.$item['contact'].PHP_EOL."Дата: ".date("d.m.Y",strtotime($item['viewDate'])), $telegram_token,$user);
                            $nn++;
                            $num++;
                            if ($nn == PAGE) {break;}
                        }



                        if ($count > PAGE) {
                            $message = 'Показать еще?';
                            $button = '{"Да":"'.$currentStep['step'].'","Нет":"0.1"}';
                            $kernel->updateTmpData($userData['userId'],'telegram','page',$page);
                        } else {
                            $message = 'Конец списка';
                            $button = '{"В начало":"0.1"}';
                        }
                        self::sendMessage($userData['userId'],  $message, $telegram_token,$user, $button);

                        exit;
                    }


                    //если листинг не пустой
                    if (!empty($listing)) {
                        $nn = 0;
                        $num = 1;
                        if ($page > 1) {$num = (($page - 1) * PAGE) + 1;}


                        foreach ($listing as $item) {

                            if ($value == 'cargo') { //для поиска груза
                                $message = "*" . $num . ".*" . PHP_EOL .
                                    "Тип поиска: груз" . PHP_EOL .
                                    "Погрузка: " . $item['start'] . PHP_EOL .
                                    "Разгрузка: " . $item['end'] . PHP_EOL .
                                    "Тип груза: " . $item['type'] . PHP_EOL .
                                    "Вес,т: " . $item['weight'] . PHP_EOL .
                                    "Объём,м3: " . $item['volume'] . PHP_EOL .
                                    "Загр\выгр: " . $item['loadType'] . PHP_EOL .
                                    "Тип трансп. : " . $item['TruckType'] . PHP_EOL .
                                    "Цена: " . $item['price'] . PHP_EOL .
                                    "Цена(без НДС): " . $item['priceNoNds'] . PHP_EOL .
                                    "Цена(c НДС): " . $item['priceNds'] . PHP_EOL .
                                    "Рейтинг: " . $item['rating'] . ' из 5' . PHP_EOL .
                                    "Комментарий: " . $item['note'] . PHP_EOL .
                                    "";
                            } elseif ($value == 'auto') { //для поиска авто

                                $message = "*" . $num . ".*" . PHP_EOL .
                                    "Тип поиска: транспорт" . PHP_EOL .
                                    "Погрузка: " . $item['load'] . PHP_EOL .
                                    "Разгрузка: " . $item['unload'] . PHP_EOL .
                                    "Тип трансп: " . $item['truckType'] . PHP_EOL .
                                    "Рейтинг: " . $item['rating'] . ' из 5' . PHP_EOL .
                                    "Комментарий: " . $item['note'] . PHP_EOL .
                                    "";
                            }

                            $contactsId = implode(",", $item["contactsID"]);
                            $button_data = "{'lid':'".$item["id"]."','step':'".$currentStep['nextStep']."','time':".time().",'id':'".$contactsId."'}";
                            $button = '{"Посмотреть контакт '.$num.'":"'.$button_data.'"}';
                            self::sendMessage($userData['userId'], $message, $telegram_token,$user, $button);
                            $nn++;
                            $num++;
                            if ($nn == PAGE) {break;}
                        }


                    } else {
                        $message = "Ошибка получения данных. Попробуйте сначала!";
                        self::sendMessage($userData['userId'],  $message, $telegram_token,$user);
                        exit;
                    }
            $currentCount = (($page - 1) * PAGE) + $count;

                    //$kernel->fnlog('Текущее '.$currentCount.' всего '.$total);
            //Определяем - есть ли еще данные для показа
            if ($currentCount < $total) {
                $message = 'Показать еще?';
                $button = '{"Да":"'.$currentStep['step'].'","Нет":"0.1"}';
                $kernel->updateTmpData($userData['userId'],'telegram','page',$page);
            } else {
                $message = 'Конец списка';
                $button = '{"В начало":"0.1"}';
            }

                    self::sendMessage($userData['userId'],  $message, $telegram_token,$user, $button);

                   // self::sendMessage($userData['userId'], "Выводим листинг", $telegram_token);

                    exit;
                    break;


                case'payment': //если

                    $userTmp = JSON_DECODE($user['tmpData'],true);
                    if (isset($userTmp['payment'])) {$orderSum = $userTmp['payment'];} else {$orderSum = PRICE;}

                    $orderCurrency = UP_CURRENCY;
                    $orderDesc = "Пополнение баланса пользователя на сумму ".$orderSum." ".$orderCurrency;

                    if ($payId = $kernel->createPayOrder($user['id'],$orderSum,$orderDesc,$orderCurrency)) {

                        $payUrl = $kernel->getUnitPayUrl($payId);
                        $user['payUrl'] = $payUrl;

                        self::sendMessage($userData['userId'], $message, $telegram_token, $user, '', 'payments');
                    } else {
                        self::sendMessage($user['userId'], 'Ошибка создания платежа! Пожалуйста, попробуйте позднее...', $telegram_token, $user);
                    }
                    exit;
                    break;


                case 'contact':
                    self::sendMessage($user['userId'], $message, $telegram_token, $user);

                    $button_data = json_decode($user['tmpData'],true);

                    //$kernel->fnlog($button_data);

                    $contactData = $kernel->getContactData(explode(",", $button_data['id']));

                    $message = '';
                    $message .= $contactData[0]['CompanyName'].PHP_EOL;
                    $message .= $contactData[0]['profile'].PHP_EOL;
                    $message .= $contactData[0]['city'].PHP_EOL;
                    $message .= 'Телефоны:'.PHP_EOL;

                    foreach ($contactData as $contact) {
                        $phones = implode(PHP_EOL,JSON_DECODE($contact['phones'],true));
                        $message .= $phones.PHP_EOL.' ('.$contact['contactName'].')'.PHP_EOL.PHP_EOL;
                    }

                    $message1=$message;
                    $message = $button_data['text'].PHP_EOL.PHP_EOL."*Контакт:*".PHP_EOL.$message;
                    $messageHistory = strstr($button_data['text'],PHP_EOL).PHP_EOL.PHP_EOL."*Контакт:*".PHP_EOL.$message1;

                    $kernel->updateTmpData($userData['userId'],'telegram','','');
                    $kernel->saveToViewsHistory($user['id'],$button_data['id'],$messageHistory,$button_data['lid']);

                    //Если контакт уже был просмотрен пользователем
                    if ((isset($button_data['repeat'])) and ($button_data['repeat'] === true)) {
                        self::sendMessage($userData['userId'], "Вы уже просматривали данный контакт", $telegram_token,$user);
                    } else {
                    //Списываем деньги и сообщаем
                    $kernel->chargeOff($user['id'], PRICE); //Списываем с баланса пользователя
                    self::sendMessage($userData['userId'], "С вашего счета списано ".PRICE." руб.", $telegram_token,$user);
                    }
                    self::sendMessage($userData['userId'], $message, $telegram_token,$user,$currentStep['stepValues']);

                    exit;
                    break;

                    default:
                    self::sendMessage($userData['userId'], 'Команда не распознана! Попробуйте сначала.', $telegram_token,$user);
                    $kernel->fnlog('Нет типа шага в БД!');
                    exit;
                    break;


            }

            self::sendMessage($userData['userId'], 'Команда не распознана! Попробуйте сначала.', $telegram_token,$user);
            exit;

        }  elseif (isset($data->callback_query)) { //если это CallBack

            //Отвечаем телеграму что получили callback
           self::callBackAnswer($data->callback_query->id,$telegram_token);
           //Удаляем кнопки
           self::edit_message_key($data->callback_query->message->message_id,$data->callback_query->from->id,$telegram_token);

            //Получаем данные текущего шага юзера для понимания где он находился ранее
            $userData = [
                'userId' => $data->callback_query->from->id,
                'messagerType' => 'telegram',
                'firstName' => 'Гость',
                'lastName' => '',
                'text' => '',
            ];
            if (isset($data->callback_query->from->first_name)) {$userData['firstName'] = $data->callback_query->from->first_name;}
            if (isset($data->callback_query->from->last_name)) {$userData['lastName'] = $data->callback_query->from->last_name;}


            $user = $kernel->get_user($userData);
            $currentStep = $kernel->get_currentStep($user['currentStep']);

            //Если это команда на просмотр карточки клиента
            if (mb_strpos($data->callback_query->data,"'id':'") !== false) {
                $button_data = JSON_DECODE(str_replace("'",'"',$data->callback_query->data),true);
                //$kernel->fnlog(str_replace("'",'"',$data->callback_query->data));

                //если время ожидания просмотра контакта истекло
                   if ((time() - intval($button_data['time'])) > 3600) {
                   self::sendMessage($userData['userId'], 'Данные возможно устарели, начните новый поиск.', $telegram_token,$user);
                   exit;
                   }

                  $button_data['text'] = $data->callback_query->message->text;
                 if ($kernel->VerifyIDCash($user['id'],$button_data['lid']) !== false) {
                    $button_data['repeat'] = true;
                 }

                //if (isset($button_data['type'])) {$kernel->fnlog($button_data['type']);}
                    //$kernel->fnlog($button_data);
                //if (($user['balance']) < PRICE) {
                    $kernel->stepUpdate($button_data['step'],$data->callback_query->from->id,'telegram');
                    $kernel->updateTmpData($user['userId'],'telegram','',JSON_ENCODE($button_data,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),true);
                    goto reread;
                //}



                exit;

            }



           if (($currentStep['step'] == '0.0') or ($currentStep['step'] == '0.1')){


               //Если это было стартовое сообщение - удаляем его
               self::deleteMessage($data->callback_query->message->message_id, $data->callback_query->from->id, $telegram_token);
           }



            $currentStep = $kernel->get_currentStep($data->callback_query->data);

            if (($currentStep['step'] == '0.0') or ($currentStep['step'] == '0.1')) {
                //Если с начала - чистим переменные
                $kernel->updateTmpData($userData['userId'], 'telegram', '', '');
            }

            if (!empty($currentStep['label'])) {self::sendMessage($data->callback_query->from->id, $currentStep['label'], $telegram_token,$user);}


            $message = '';
            if (!empty($currentStep['text'])) {
                $message = $currentStep['text'];
            }



            //$kernel->fnlog($currentStep['stepValues']);

            $kernel->stepUpdate($currentStep['step'],$data->callback_query->from->id,'telegram');

            if ($currentStep['typeStep'] == 'buttons') {
                self::sendMessage($data->callback_query->from->id, $message, $telegram_token, $user,$currentStep['stepValues']);
            } elseif ($currentStep['typeStep'] == 'input') {
                self::sendMessage($data->callback_query->from->id, $message, $telegram_token, $user);
            } else {
                $autoreread = true;goto reread;
            }

        }


    }

    /**  Функция отправки сообщений */
    public function sendMessage($chatId="",$message="",$token="",$userData="",$keyboards="",$type="",$deleteKey=false)
    {
        global $kernel;
        global $telegram_token;
        if (empty($token)) ($token = $telegram_token);

        //Перезаписываем константы
        $message = str_replace('{PhoneNumber}','['.SUPPORT.'](tel:'.SUPPORT.')',$message);
        $message = str_replace('{botname}',BOTNAME,$message);
        $message = str_replace('{price}',PRICE,$message);

        //перезаписываем разметку
        $message = str_replace(['<b>','</b>'],'*',$message);

        $message = htmlspecialchars_decode($message);


        if (isset($userData['firstName'])) {$message = str_replace('{username}',$userData['firstName'],$message);}
        if (isset($userData['balance'])) {$message = str_replace('{balance}',$userData['balance'],$message);}
        if ((isset($userData['tmpData'])) and (!empty($userData['tmpData']))) {

        $tmpData = JSON_DECODE($userData['tmpData'],true);
        if (isset($tmpData['radius'])) {
            $message = str_replace('{radius}',$tmpData['radius'],$message);
        } else {
            $message = str_replace('{radius}',RADIUS,$message);
        }

        if (isset($tmpData['payment'])) {$message = str_replace('{payment}',$tmpData['payment'],$message);}

        }


        if ((!empty($keyboards)) and (empty($type))) {

            $keyboards = JSON_DECODE($keyboards, true);
            foreach ($keyboards as $data => $key) {

                $keyboard['inline_keyboard'][] = [
                    ['text' => $data, 'callback_data' => $key]
                ];

            }
            $keyboard['one_time_keyboard'] = false;
            $keyboard['resize_keyboard'] = true;

        } elseif ((empty($keyboards)) and (!empty($type))) {

            switch ($type){
                case'payments':

                    $keyboard['inline_keyboard'][] = [
                        ['text' => 'Пополнить баланс', 'url' => $userData['payUrl']]
                    ];
                    $keyboard['inline_keyboard'][] = [
                   ['text' => 'Отмена (в начало)', 'callback_data' => '0.1']
                   ];

                    $keyboard['one_time_keyboard'] = false;
                    $keyboard['resize_keyboard'] = true;
                    break;

                case'phone':
                    $keyboard['keyboard'][] = [
                    ['text'=>'Отправить телефон','request_contact'=>true],
                    ];

                   // $keyboard['selective'] = true;
                    $keyboard['one_time_keyboard'] = false;
                    $keyboard['resize_keyboard'] = true;
                    break;

                default:
                    $keyboard= '';
                    break;

            }


        }


        if (empty($keyboard)) {

            $keyboard['keyboard'][] = [
                ['text'=>'В начало'],
                ['text'=>'Техподдержка'],
            ];
            $keyboard['one_time_keyboard'] = false;
            $keyboard['resize_keyboard'] = true;

            $request_params = array(
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => JSON_ENCODE($keyboard),


            );
        } else {
            $request_params = array(
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => JSON_ENCODE($keyboard),
            );

        }

        if ($deleteKey == true) {

            //$kernel->fnlog('Удаление кнопки контакт');

            $keyboard = [];

            $keyboard['keyboard'][] = [
                ['text'=>'В начало'],
                ['text'=>'Техподдержка'],
            ];
            $keyboard['one_time_keyboard'] = true;
            $keyboard['resize_keyboard'] = true;

            $request_params = array(
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => JSON_ENCODE($keyboard),

            );

        }


        $get_params = http_build_query($request_params);
        $url = 'https://api.telegram.org/bot'.$token.'/sendMessage?' . $get_params;

        $result = $kernel->curl($url);
        //return $result;
    }

    /** удаление кнопки в телеграмм */
    private function edit_message_key($message_id,$chat_id,$telegram_token) {
        global $kernel;


            $keyboard = [
                'inline_keyboard' => [
                    [

                    ]
                ]
            ];


        $encodedKeyboard = json_encode($keyboard);

        //отправляем ответное сообщение
        $request_params = array(
            'chat_id' => $chat_id,
            'message_id' => intval($message_id),
            'reply_markup' => $encodedKeyboard,
        );

        $get_params = http_build_query($request_params);
        $url = 'https://api.telegram.org/bot'.$telegram_token.'/editMessageReplyMarkup?' . $get_params;
        //file_put_contents('./url.txt', $url);

        $kernel->curl($url);
    }

    /**  ответ при CallBack */
    private function callBackAnswer($callback_query_id,$telegram_token){
    global $kernel;

        $request_params = array(
            'callback_query_id' => $callback_query_id,
            'cache_time'=>0,

        );
        $get_params = http_build_query($request_params);
        $url = 'https://api.telegram.org/bot'.$telegram_token.'/answerCallbackQuery?' . $get_params;
        $kernel->curl($url);

    }

    /** удаление сообщение в телеграмм */
    private function  deleteMessage($message_id,$chat_id,$telegram_token) {
        global $kernel;




        //отправляем ответное сообщение
        $request_params = array(
            'chat_id' => $chat_id,
            'message_id' => intval($message_id),
        );

        $get_params = http_build_query($request_params);
        $url = 'https://api.telegram.org/bot'.$telegram_token.'/deleteMessage?' . $get_params;

        $kernel->curl($url);




    }




}