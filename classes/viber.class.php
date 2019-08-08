<?php

/** Класс для обработки функционала бота VIber
 *
 */

class viber{

    /** Обработка входящих сообщений */
    public function incoming()
    {
        global $kernel;
        //если нет входящих параметров - отказ
        if (!isset($_REQUEST)) {
            $kernel->deny();
        }

        $data2 = JSON_ENCODE(json_decode(file_get_contents('php://input',true)),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents('./viber_data_log.txt', $data2);

        $data = json_decode(file_get_contents('php://input')); //читаем данные от Telegram
        if (empty($data)) {$kernel->deny();} //если пустые данные или нет сообщения - отказ

/*
if (isset($data->message)) {
    $user_id = $data->sender->id;
    $messageType = $data->message->type;
    $chat_id = $data->chat_hostname;
    $message = $data->message->text;

    self::sendMessage($user_id,$message);
}
*/
        self::parseMessage($data);

        header("HTTP/1.0 200 OK");
        echo "OK";
        exit;

    }

    /**  Разбираем сообщение для отправки в CRM */
    private function parseMessage($data='')
    {
        global $kernel;
        global $viber_token;

        if (empty($data)) $kernel->deny(); //если пустые данные - отказ
        //$kernel->fnlog('Входящее от Viber '.json_encode($data,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $nn = 0;
        $contact_message = false;
        $autoreread = false;


        if (isset($data->message)) { //если это сообщение

            if (isset($data->message->text) and (mb_strpos($data->message->text,'#CallBackButton#') === false)) {


            If ((isset($data->message->text)) and (($data->message->text == '/start') or ($data->message->text == 'В начало'))) {
                $kernel->stepUpdate('0.0', $data->sender->id, 'viber');
            }
            If ((isset($data->message->text)) and ($data->message->text == 'Техподдержка')) {
                $kernel->stepUpdate('3.0', $data->sender->id, 'viber');
            }


            reread: //Автоматически перечитываем параметры по команде ниже по коду
            $nn++;
            if ($nn > 10) {

                self::sendMessage($data->sender->id, 'Команда не распознана! Попробуйте сначала.');
                $kernel->fnlog('Сработал контроль цикла');
                exit;
            } //контроль зацикливания - выбивает после 10 возвратов

            if (isset($data->message->text) and (mb_strpos($data->message->text,'#CallBackButton#') === false)) {

                if (!isset($data->message->text)) {
                    $text = '';
                } else {
                    $text = $data->message->text;
                }

                $userData = [
                    'userId' => $data->sender->id,
                    'messagerType' => 'viber',
                    'firstName' => 'Гость',
                    'lastName' => '',
                    'text' => $text,
                ];

                if (isset($data->sender->name)) {
                    $userData['firstName'] = $data->sender->name;
                }
                //if (isset($data->message->from->last_name)) {$userData['lastName'] = $data->message->from->last_name;}

            } elseif (isset($data->message->text) and (mb_strpos($data->message->text,'#CallBackButton#') !== false)) {
                $userData = [
                    'userId' => $data->sender->id,
                    'messagerType' => 'viber',
                    'firstName' => 'Гость',
                    'lastName' => '',
                    'text' => '',
                ];
                if (isset($data->sender->name)) {
                    $userData['firstName'] = $data->sender->name;
                }
                //if (isset($data->callback_query->from->last_name)) {$userData['lastName'] = $data->callback_query->from->last_name;}
            }


            //Получаем данные юзера
            $user = $kernel->get_user($userData);

            //Если это новый юзер подставляем "В начало"
            if (isset($user['newUser'])) {
                $currentStep = $kernel->get_currentStep($user['currentStep']);
                $userData['text'] = 'В начало';
            }


            //Получаем данные текущего шага юзера
            $currentStep = $kernel->get_currentStep($user['currentStep']);

            //Если с начала - чистим переменные
            if (($currentStep['step'] == '0.1') or ($currentStep['step'] == '0.0')) {
                //$kernel->fnlog('Чистим временные данные');
                $kernel->updateTmpData($userData['userId'], 'viber', '', '');
            }


            //Если передан контакт
            if ((isset($data->message->contact)) and ($contact_message == false)) {

                $phoneStatus = $kernel->saveUserTel($data->sender->id, 'viber', $data->message->contact->phone_number);

                if ($phoneStatus == true) {
                    self::sendMessage($data->sender->id, "Номер телефона успешно сохранен!", $user);
                    $contact_message = true;

                    if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {
                        $kernel->stepUpdate($currentStep['nextStep'], $user['userId'], 'viber');

                        $autoreread = true;
                        goto reread;
                    } else {
                        $kernel->stepUpdate('0.0', $user['userId'], 'viber');
                        $autoreread = true;
                        goto reread;
                    }


                } else {
                    self::sendMessage($data->sender->id, "Не удалось сохранить номер телефона!", $viber_token, $user);
                    exit;
                }

            }

            //Устанавливаем текст из шага в сообщение, если есть
            if (!empty($currentStep['text'])) {
                $message = $currentStep['text'];
            } else {
                $message = '';
            }

            //Если не распознали действие - оповещаем пользователя
            if (($currentStep['typeStep'] != 'input') and (!empty($userData['text']))
                and ($userData['text'] != '/start')
                and ($userData['text'] != 'В начало')
                and ($userData['text'] != 'Техподдержка')
                and ($autoreread != true)
            ) {
                self::sendMessage($data->sender->id, 'Извините, команда не распознана!', $viber_token, $user);
                exit;
            }

                //проброс шага, если это не контакт и есть флаг проброса
                $button_data = json_decode($user['tmpData'],true);
                if (($currentStep['typeStep'] != 'contact') and (isset($button_data['repeat'])) and ($button_data['repeat'] === true)) {
                    $kernel->stepUpdate($currentStep['nextStep'],$user['userId'],'viber');
                    //$kernel->fnlog('Установлен шаг '.$currentStep['nextStep']);
                    $autoreread = true;goto reread;
                }

            //Смотрим тип текущего шага
            switch ($currentStep['typeStep']) {

                case 'buttons':
                    self::sendMessage($userData['userId'], $message, $viber_token, $user, $currentStep['stepValues']);
                    exit;
                    break;

                case 'input':

                    //Проверяем параметры ввода

                    if (empty($userData['text'])) {
                        //Для кнопки телефона
                        if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {
                            $type = 'phone';
                        } else {
                            $type = '';
                        }
                        self::sendMessage($userData['userId'], $currentStep['text'], $viber_token, $user, '', $type);
                        exit;
                    }

                    $messageParam = $kernel->verifyInput($userData['text'], $currentStep['stepValues'], $currentStep['nextStep']);

                    if ($messageParam) {

                        //Если новый поиск - чистим данные поиска
                        if (($messageParam['status'] === true) and ($messageParam['input_value'] == 'starting')) {
                            $kernel->updateTmpData($userData['userId'], 'viber', '', '');
                        }

                        if (($messageParam['status'] === true) and ($messageParam['input_value'] == 'radius')) {
                            if ((isset($user['tmpData'])) and (!empty($user['tmpData']))) {
                                $tmpData = JSON_DECODE($user['tmpData'], true);
                                if (isset($tmpData['radius'])) {
                                    if (intval($tmpData['radius']) > intval($messageParam['text'])) {
                                        $messageParam['status'] = false;
                                        $messageParam['message'] = "Проверьте правильность введенных данных!";
                                    }
                                } else {
                                    if (intval(RADIUS) > intval($messageParam['text'])) {
                                        $messageParam['status'] = false;
                                        $messageParam['message'] = "Проверьте правильность введенных данных!";
                                    }
                                }

                            }
                        }

                        if ($messageParam['status'] === true) {

                            if ($messageParam['input_value'] == 'phone') {

                                $phoneStatus = $kernel->saveUserTel($data->sender->id, 'viber', $messageParam['text']);

                                if ($phoneStatus == true) {
                                    self::sendMessage($data->sender->id, "Номер телефона успешно сохранен!", $viber_token, $user, '', '', true);
                                    $contact_message = true;

                                    if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {
                                        $kernel->stepUpdate($currentStep['nextStep'], $user['userId'], 'viber');
                                        $autoreread = true;
                                        goto reread;
                                    } else {
                                        $kernel->stepUpdate('0.0', $user['userId'], 'viber');
                                        $autoreread = true;
                                        goto reread;
                                    }


                                } else {
                                    self::sendMessage($data->sender->id, "Не удалось сохранить номер телефона!", $viber_token, $user);
                                    exit;
                                }

                            }

                            $kernel->updateTmpData($userData['userId'], 'viber', $messageParam['input_value'], $messageParam['text']);
                            $currentStep = $kernel->get_currentStep($messageParam['step']);
                            //$kernel->fnlog(JSON_ENCODE($currentStep, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                            if (!empty($currentStep['text'])) {
                                $message = $currentStep['text'];
                            } else {
                                $message = '';
                            }
                            $kernel->stepUpdate($currentStep['step'], $userData['userId'], 'viber');

                            //Автопереход шага
                            if ($currentStep['typeStep'] != 'input') {
                                $autoreread = true;
                                goto reread;
                            }

                        } else {

                            $message = $messageParam['message'] . PHP_EOL . $message;

                            if (trim($currentStep['stepValues']) == '{"phone":"phone"}') {
                                $type = 'phone';
                                self::sendMessage($userData['userId'], $message, $viber_token, $user, '', $type);
                                exit;
                            }

                        }

                        self::sendMessage($userData['userId'], $message, $viber_token, $user);
                    } else {
                        $kernel->fnlog('Не удалось распознать параметры ввода');
                    }
                    exit;
                    break;

                //Если требуется запрос в БД
                case 'query':

                    $type = '';
                    $value = '';
                    //Получаем тип запроса и исследуемый параметр
                    foreach (json_decode($currentStep['stepValues'], true) as $types => $key) {
                        $type = $types;
                        $value = $key;
                        break;
                    }

                    //$kernel->fnlog('query '.$type.' '.$value);

                    switch ($type) {
                        //Запрос на поиск массива значений
                        case'search':

                            $search_data = $kernel->get_search_data($user['tmpData'], $value);


                            if (count($search_data) > 0) {

                                $kernel->stepUpdate($currentStep['step'] . '.1', $data->sender->id, 'viber');
                                $currentStep = $kernel->get_currentStep($currentStep['step']);


                            } elseif (count($search_data) == 0) {

                                $kernel->stepUpdate($currentStep['step'] . '.0', $data->sender->id, 'viber');
                                $currentStep = $kernel->get_currentStep($currentStep['step']);

                            } else {
                                $kernel->fnlog('Не удалось произвести поиск ' . $value);
                                exit;
                            }

                        //Запрос на количество найденных значений
                        case 'count':

                            $count_data = $kernel->get_count_data($user['tmpData'], $value, $user);


                            if ($count_data === false) {
                                $message = 'Не удалось определить город, пожалуйста напишите город в правильном формате.';
                                $button = '{"Попробовать еще раз":"'.$currentStep['nextStep'].'","В начало":"0.1"}';
                                self::sendMessage($userData['userId'], $message, $viber_token,$user,$button);

                                $kernel->stepUpdate('0.1',$userData['userId'],'viber');
                                exit;
                            }
                            
                            

                            if ($count_data > 0) {

                                if (!empty($currentStep['text'])) {
                                    self::sendMessage($userData['userId'], str_replace('{count}', $count_data, $currentStep['text']), $viber_token, $user);
                                }

                                //$kernel->fnlog('Обновляем шаг ' . $currentStep['step'] . '.1' . ' ' . $userData['userId']);

                                $kernel->stepUpdate($currentStep['step'] . '.1', $userData['userId'], 'viber');
                                $autoreread = true;
                                goto reread;
                                //$currentStep = $kernel->get_currentStep($currentStep['step']);


                            } elseif ($count_data == 0) {

                                $kernel->stepUpdate($currentStep['step'] . '.0', $userData['userId'], 'viber');
                                $autoreread = true;
                                goto reread;
                                //$currentStep = $kernel->get_currentStep($currentStep['step']);

                            } else {
                                $kernel->fnlog('Не удалось произвести подсчет количества ' . $value);
                                exit;
                            }

                            break;

                        //Запрос на получение единичного значения
                        case 'value':

                            $value_data = $kernel->get_value_data($user['tmpData'], $value, $user);

                            if ($value == 'phone') {
                               // $kernel->fnlog('Проверяем телефон пользователя');
                                if (!empty($value_data)) {
                                   // $kernel->fnlog('Есть телефон пользователя');
                                    $kernel->stepUpdate($currentStep['step'] . '.1', $user['userId'], 'viber');
                                    $autoreread = true;
                                    goto reread;
                                } elseif (empty($value_data)) {
                                    //$kernel->fnlog('Нет телефона пользователя');
                                    $kernel->stepUpdate($currentStep['step'] . '.0', $user['userId'], 'viber');
                                    $autoreread = true;
                                    goto reread;
                                } elseif ($value_data == false) {
                                    $kernel->fnlog('Не удалось проверить телефон пользователя');
                                }
                            }

                            if ($value_data == true) {
                                $kernel->stepUpdate($currentStep['step'] . '.1', $userData['userId'], 'viber');
                                goto reread;
                            } elseif ($value_data == false) {
                                $kernel->stepUpdate($currentStep['step'] . '.0', $userData['userId'], 'viber');
                                goto reread;
                            }

                            break;


                    }

                    self::sendMessage($userData['userId'], "Команда не распознана, попробуйте сначала!", $viber_token, $user);
                    exit;

                    break;

                case'list':

                    $type = '';
                    $value = '';
                    //Получаем тип листинга и исследуемый параметр
                    foreach (json_decode($currentStep['stepValues'], true) as $types => $key) {
                        $type = $types;
                        $value = $key;
                        break;
                    }


                    /** Если это возврат на контакт - показываем полное описание контакта */
                    $userTmp = JSON_DECODE($user['tmpData'],true);
                    if ((isset($userTmp['lid'])) and (isset($userTmp['contact'])) and ($userTmp['contact'] !== false)) {


                        $messData = $kernel->getCashMessage($userTmp['lid']);
                        $messData = JSON_DECODE($messData,true);

                        $button = $messData['button'];
                        $message = $messData['message'];
                        $message = trim(str_replace('<br>',PHP_EOL,$message));

                        if (
                            (isset($userTmp['starting'])) and (!empty($userTmp['starting']))
                            and (isset($userTmp['next'])) and ($userTmp['next'] === true)
                        )
                        {
                            $nextButton = '"Показать еще":"' . $currentStep['step'] . '",';
                        } else {$nextButton = '';}

                        $button_data = '{"Посмотреть контакт?":"'.$button.'",'.$nextButton.'"В начало":"0.1"}';

                        self::sendMessage($userData['userId'], "Полный текст объявления:".PHP_EOL.PHP_EOL.$message, $viber_token, $user,$button_data);

                        exit;

                    }

                    $listing = $kernel->get_search_data($user['tmpData'], $value, $user);
                    


                    $total = $listing['totalCount'];
                    unset($listing['totalCount']);

                    $count = count($listing);

                    //$userTmp = JSON_DECODE($user['tmpData'],true);
                    if (isset($userTmp['page'])) {$page = $userTmp['page'] + 1;} else {$page = 1;}

                    if ($value == 'history') {
                        $nn = 0;
                        $num = 1;
                        if ($page > 1) {
                            $num = (($page - 1) * PAGE) + 1;
                        }
                        foreach ($listing as $item) {

                            self::sendMessage($userData['userId'], "" . $num . ") Просмотренный контакт:"
                                . PHP_EOL . $item['contact'] . PHP_EOL . "Дата: " . date("d.m.Y", strtotime($item['viewDate'])), $viber_token, $user);
                            $nn++;
                            $num++;
                            if ($nn == PAGE) {
                                break;
                            }
                        }


                        if ($count > PAGE) {
                            $message = 'Показать еще?';
                            $button = '{"Да":"' . $currentStep['step'] . '","Нет":"0.1"}';
                            $kernel->updateTmpData($userData['userId'], 'viber', 'page', $page);
                        } else {
                            $message = 'Конец списка';
                            $button = '{"В начало":"0.1"}';
                        }

                        self::sendMessage($userData['userId'], $message, $viber_token, $user, $button);

                        exit;
                    }


                    if (!empty($listing)) {
                        $nn = 0;
                        $num = 1;
                        if ($page > 1) {
                            $num = (($page - 1) * PAGE) + 1;
                        }

                        //Создаем карусель в сообщении
                        $Carousel = [];
                        foreach ($listing as $item) {

                            if ($value == 'cargo') {
                                $message = " " .
                                    "Тип поиска: груз" . "<br>" .
                                    "Погрузка: " . $item['start'] . "<br>" .
                                    "Разгрузка: " . $item['end'] . "<br>" .
                                    "Тип груза: " . $item['type'] . "<br>" .
                                    "Вес,т: " . $item['weight'] . "<br>" .
                                    "Объём,м3: " . $item['volume'] . "<br>" .
                                    "Загр\выгр: " . $item['loadType'] . "<br>" .
                                    "Тип трансп. : " . $item['TruckType'] . "<br>" .
                                    "Цена: " . $item['price'] . "<br>" .
                                    "Цена(без НДС): " . $item['priceNoNds'] . "<br>" .
                                    "Цена(c НДС): " . $item['priceNds'] . "<br>" .
                                    "Рейтинг: " . $item['rating'] . ' из 5' . "<br>" .
                                    "Комментарий: " . $item['note'] . "<br>";
                                   // "<br>" .
                                   // "<font color='#ccc'>Нажмите, чтобы посмотреть</font>";
                            } elseif ($value == 'auto') {

                                $message = " " .
                                    "Тип поиска: транспорт" . "<br>" .
                                    "Погрузка: " . $item['load'] . "<br>" .
                                    "Разгрузка: " . $item['unload'] . "<br>" .
                                    "Тип трансп: " . $item['truckType'] . "<br>" .
                                    "Рейтинг: " . $item['rating'] . ' из 5' . "<br>" .
                                    "Комментарий: " . $item['note'] . "<br>";
                                   // "<br>" .
                                   // "<font color='#ccc'>Нажмите, чтобы посмотреть</font>";
                            }

                            $contactsId = implode(",", $item["contactsID"]);
                            $button_data = "{'lid':'".$item["id"]."','step':'".$currentStep['step']."','time':".time().",'id':'".$contactsId."'}";
                            $button_data2 = "{'lid':'".$item["id"]."','step':'".$currentStep['nextStep']."','time':".time().",'id':'".$contactsId."'}";

                            //$button_data = "{'lid':'".$item["id"]."','step':'".$currentStep['nextStep']."','time':".time().",'id':'".$contactsId."'}";
                            //$button_data = "{'lid':'".$item["id"]."','step':'".$currentStep['step']."','time':".time().",'show':1}";
                            //$button = '{"Посмотреть контакт":"' . $button_data . '"}';
                            //self::sendMessage($userData['userId'], $message, $viber_token, $user, $button);
                            $messageCut = $kernel->CutStr($message,300);

                            $messageCut = "<font color='#ccc'>Нажмите для просмотра</font><br>"."<b>" . $num . ".</b>" . " " .$messageCut;



                            $Carousel[] =   [
                                           'text' => $messageCut,
                                           'callback' => '#CallBackButton#' . $button_data,
                                            ];

                            $cash_message = JSON_ENCODE(
                                [
                                    'message'=>$message,
                                    'button' =>'#CallBackButton#' .$button_data2,
                                ],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                            );

                            $kernel->saveCashMessage($item["id"],$cash_message);

                            $nn++;
                            $num++;
                            if ($nn == PAGE) {
                                break;
                            }
                        }
                        self::sendMessage($userData['userId'], '', $viber_token, $user,$Carousel,'carousel');


                    } else {
                        $message = "Ошибка получения данных. Попробуйте сначала!";
                        self::sendMessage($userData['userId'], $message, $viber_token, $user);
                        exit;
                    }
                    $currentCount = (($page - 1) * PAGE) + $count;
                    if ($currentCount < $total) {
                        $message = 'Показать еще?';
                        $button = '{"Да":"' . $currentStep['step'] . '","Нет":"0.1"}';
                        $kernel->updateTmpData($userData['userId'], 'viber', 'next', true);
                        $kernel->updateTmpData($userData['userId'], 'viber', 'page', $page);
                    } else {
                        $message = 'Конец списка';
                        $button = '{"В начало":"0.1"}';
                        $kernel->updateTmpData($userData['userId'], 'viber', 'next', false);
                    }

                    self::sendMessage($userData['userId'], $message, $viber_token, $user, $button);

                    // self::sendMessage($userData['userId'], "Выводим листинг", $viber_token);

                    exit;
                    break;


                case'payment':
                    $userTmp = JSON_DECODE($user['tmpData'],true);
                    if (isset($userTmp['payment'])) {$orderSum = $userTmp['payment'];} else {$orderSum = PRICE;}

                    $orderCurrency = UP_CURRENCY;
                    $orderDesc = "Пополнение баланса пользователя на сумму ".$orderSum." ".$orderCurrency;

                    if ($payId = $kernel->createPayOrder($user['id'],$orderSum,$orderDesc,$orderCurrency)) {

                        $payUrl = $kernel->getUnitPayUrl($payId);
                        $user['payUrl'] = $payUrl;

                        self::sendMessage($userData['userId'], $message, $viber_token, $user, '', 'payments');
                    } else {
                        self::sendMessage($user['userId'], 'Ошибка создания платежа! Пожалуйста, попробуйте позднее...', $viber_token, $user);
                    }
                    exit;
                    break;


                case 'contact':
                    self::sendMessage($user['userId'], $message, $viber_token, $user);
                    $button_data = json_decode($user['tmpData'], true);



                    //$message = $kernel->getContactData($button_data['id'], $button_data['typeQuery']);

                    $contactData = $kernel->getContactData(explode(",", $button_data['id']));
                    $MessageData = $kernel->getCashMessage($button_data['lid']);
                    $MessageData = JSON_DECODE($MessageData,true);


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
                    $message = $MessageData['message'].PHP_EOL.PHP_EOL."<b>Контакт:</b>".PHP_EOL.$message;
                    $messageHistory = $MessageData['message'].PHP_EOL.PHP_EOL."Контакт:".PHP_EOL.$message1;


                    $kernel->saveToViewsHistory($user['id'],$button_data['id'],$messageHistory,$button_data['lid']);

                    if ((isset($button_data['repeat'])) and ($button_data['repeat'] === true)) {
                        self::sendMessage($userData['userId'], "Вы уже просматривали данный контакт", $viber_token,$user);
                    } else {
                        //Списываем деньги и сообщаем
                        $kernel->chargeOff($user['id'], PRICE); //Списываем с баланса пользователя
                        self::sendMessage($userData['userId'], "С вашего счета списано ".PRICE." руб.", $viber_token,$user);
                    }
                    //self::sendMessage($data->sender->id, "*Просмотр контакта:*" . PHP_EOL . $message, $viber_token, $user);
                    self::sendMessage($userData['userId'], $message, $viber_token,$user,$currentStep['stepValues']);

                    exit;
                    break;

                default:
                    self::sendMessage($userData['userId'], 'Команда не распознана! Попробуйте сначала.', $viber_token, $user);
                    $kernel->fnlog('Нет типа шага в БД!');
                    exit;
                    break;


            }

            self::sendMessage($userData['userId'], 'Команда не распознана! Попробуйте сначала.', $viber_token, $user);
            exit;

        } elseif
            (isset($data->message->text) and (mb_strpos($data->message->text,'#CallBackButton#') !== false)) { //если это CallBack

            $callBackData = str_replace('#CallBackButton#','',$data->message->text);
            //Получаем данные текущего шага юзера для понимания где он находился ранее
            $userData = [
                'userId' => $data->sender->id,
                'messagerType' => 'viber',
                'firstName' => 'Гость',
                'lastName' => '',
                'text' => '',
            ];
                if (isset($data->sender->name)) {
                    $userData['firstName'] = $data->sender->name;
                }

                /*
            if (isset($data->callback_query->from->last_name)) {
                $userData['lastName'] = $data->callback_query->from->last_name;
            }
                */


            $user = $kernel->get_user($userData);
            $currentStep = $kernel->get_currentStep($user['currentStep']);

            //Если это команда на просмотр карточки клиента
            if (mb_strpos($callBackData, "'lid':'") !== false) {
                $button_data = JSON_DECODE(str_replace("'", '"', $callBackData), true);
                //$kernel->fnlog(str_replace("'", '"', $callBackData));

                //если время ожидания просмотра контакта истекло
                if ((time() - intval($button_data['time'])) > 3600) {
                    self::sendMessage($userData['userId'], 'Данные возможно устарели, начните новый поиск.', $viber_token, $user);
                    exit;
                }


                //if (isset($button_data['type'])) {$kernel->fnlog($button_data['type']);}
                //$button_data['text'] = $data->callback_query->message->text;
                if ($kernel->VerifyIDCash($user['id'],$button_data['lid']) !== false) {
                    $button_data['repeat'] = true;
                }

                //if (($user['balance']) < PRICE) {
                $kernel->stepUpdate($button_data['step'], $data->sender->id, 'viber');


                if (!empty($user['tmpData'])) {

                    $userTmp = JSON_DECODE($user['tmpData'],true);

                    if (isset($userTmp['starting'])) {$button_data['starting'] = $userTmp['starting'];}
                    if (isset($userTmp['destination'])) {$button_data['destination'] = $userTmp['destination'];}
                    if (isset($userTmp['tonnage'])) {$button_data['tonnage'] = $userTmp['tonnage'];}
                    if (isset($userTmp['page'])) {$button_data['page'] = $userTmp['page'];}
                    if (isset($userTmp['next'])) {$button_data['next'] = $userTmp['next'];}
                    $button_data['contact'] = true;


                }
                $kernel->updateTmpData($user['userId'], 'viber', '', JSON_ENCODE($button_data,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
                goto reread;
                //}


                /*
                                $message = $kernel->getContactData($button_data['id'],$button_data['typeQuery']);
                                $kernel->saveToViewsHistory($user['id'],$button_data['id'],$message);
                                $kernel->chargeOff($user['id'],PRICE); //Списываем с баланса пользователя
                                self::sendMessage($data->callback_query->from->id, $message, $viber_token,$user);
                */
                exit;

            } else {$kernel->updateTmpData($userData['userId'], 'viber', 'contact', false);}

            $currentStep = $kernel->get_currentStep($callBackData);

            if (($currentStep['step'] == '0.0') or ($currentStep['step'] == '0.1')) {
                //Если с начала - чистим переменные
                $kernel->updateTmpData($userData['userId'], 'viber', '', '');
            }

            if (!empty($currentStep['label'])) {
                self::sendMessage($data->sender->id, $currentStep['label'], $viber_token, $user);
            }


            $message = '';
            if (!empty($currentStep['text'])) {
                $message = $currentStep['text'];
            }


            //$kernel->fnlog($currentStep['stepValues']);

            $kernel->stepUpdate($currentStep['step'], $data->sender->id, 'viber');

            if ($currentStep['typeStep'] == 'buttons') {
                self::sendMessage($data->sender->id, $message, $viber_token, $user, $currentStep['stepValues']);
            } elseif ($currentStep['typeStep'] == 'input') {
                self::sendMessage($data->sender->id, $message, $viber_token, $user);
            } else {
                $autoreread = true;
                goto reread;
            }

        }
    }
        elseif (((!isset($data->message))) and (isset($data->event))) {

            switch ($data->event){
                case 'unsubscribed':
                $kernel->fnlog('Отписался '.$data->user_id);
                    break;

                case 'subscribed':
                    $kernel->fnlog('Подписался '.$data->user_id);



                    $userData = [
                        'userId' => $data->user_id,
                        'messagerType' => 'viber',
                        'firstName' => 'Гость',
                        'lastName' => '',
                        'text' => '',
                    ];
                    if (isset($data->sender->name)) {
                        $userData['firstName'] = $data->sender->name;
                    }

                    $user = $kernel->get_user($userData);
                    $currentStep = $kernel->get_currentStep($user['currentStep']);
                    self::sendMessage($userData['userId'], $currentStep['text'], $viber_token, $user);


                    break;

                default:
                    break;


            }

        }

    }

    /** Регистрация адреса webhook */
    public function webhookRegister(){
        global $kernel;
        global $viber_token;

        $url = 'https://chatapi.viber.com/pa/set_webhook';

        $post = [
        'url'  => "https://".$_SERVER['HTTP_HOST']."/".$viber_token,
        'event_types' => [
            "message",
            "subscribed",
            "unsubscribed",
        ],
        'send_name'=> true,
        'send_photo'=> false,
        ];

        $headers[] = 'X-Viber-Auth-Token: '.$viber_token;

        $request = $kernel->curlPost($url,JSON_ENCODE($post),$headers);

        //$kernel->fnlog($request);
    }

    /** Посылаем сообщение пользователю */
    public function sendMessage($userId,$message,$token="",$userData="",$keyboards="",$type="",$deleteKey=false)
    {
        global $kernel;
        global $viber_token;
        if (empty($token)) ($token = $viber_token);

        $url = 'https://chatapi.viber.com/pa/send_message';
        $headers[] = 'X-Viber-Auth-Token: ' . $viber_token;

        $type_message = 'text'; //по умолчанию текст

        //Перезаписываем константы
        $message = str_replace('{PhoneNumber}', SUPPORT, $message);
        $message = str_replace('{botname}', BOTNAME, $message);
        $message = str_replace('{price}', PRICE, $message);

        //перезаписываем разметку
        $message = str_replace(['<b>','</b>'],'',$message);
        $message = str_replace('<br>',PHP_EOL,$message);

        if (isset($userData['firstName'])) {
            $message = str_replace('{username}', $userData['firstName'], $message);
        }
        if (isset($userData['balance'])) {
            $message = str_replace('{balance}', $userData['balance'], $message);
        }
        if ((isset($userData['tmpData'])) and (!empty($userData['tmpData']))) {

            $tmpData = JSON_DECODE($userData['tmpData'], true);
            if (isset($tmpData['radius'])) {
                $message = str_replace('{radius}', $tmpData['radius'], $message);
            } else {
                $message = str_replace('{radius}', RADIUS, $message);
            }

            if (isset($tmpData['payment'])) {
                $message = str_replace('{payment}', $tmpData['payment'], $message);
            }

        }

        //Формируем кнопки по умолчанию
        if ((empty($keyboards)) and (empty($type))) {

            $keyboard = [
                'Type' => "keyboard",
                //'DefaultHeight'=>false,
                "BgColor" => "#cccccc",
                'Buttons' => [
                    ['Silent' => true,
                        "Columns" => 3,
                        "Rows" => 1,
                        "ActionType" => "reply",
                        "ActionBody" => "В начало",
                        'DefaultHeight' => false,
                        "BgColor" => "#ffffff",
                        "Text" => "В начало",
                        "TextSize" => "large"

                    ],
                    ['Silent' => true,
                        "Columns" => 3,
                        "Rows" => 1,
                        "ActionType" => "reply",
                        "ActionBody" => "Техподдержка",
                        'DefaultHeight' => false,
                        "BgColor" => "#ffffff",
                        "Text" => "Техподдержка",
                        "TextSize" => "large"

                    ],


                ],

            ];

        } elseif (!empty($type)) { //Формируем кнопки в зависимости от типа

                        switch ($type){
                            case'payments':

                                $keyboard = [
                                    'Type' => "keyboard",
                                    //'DefaultHeight'=>false,
                                    "BgColor" => "#cccccc",
                                    'Buttons' => [
                                        ['Silent' => true,
                                            "Columns" => 3,
                                            "Rows" => 1,
                                            "ActionType" => "open-url",
                                            "ActionBody" => $userData['payUrl'],
                                            'DefaultHeight' => false,
                                            "BgColor" => "#8176d6",
                                            "Text" => "<font color='#ffffff'>Пополнить баланс</font>",
                                            "TextSize" => "large",
                                            "OpenURLType"=>"external",
                                            "OpenURLMediaType"=>"not-media"

                                        ],
                                        ['Silent' => true,
                                            "Columns" => 3,
                                            "Rows" => 1,
                                            "ActionType" => "reply",
                                            "ActionBody" => "#CallBackButton#0.1",
                                            'DefaultHeight' => false,
                                            "BgColor" => "#8176d6",
                                            "Text" => "<font color='#ffffff'>Отмена (в начало)</font>",
                                            "TextSize" => "large"

                                        ],


                                    ],

                                ];

                                break;

                            case'phone':
                                $keyboard = [
                                    'Type' => "keyboard",
                                    //'DefaultHeight'=>false,
                                    "BgColor" => "#cccccc",
                                    'Buttons' => [
                                        ['Silent' => true,
                                            "Columns" => 3,
                                            "Rows" => 1,
                                            "ActionType" => "share-phone",
                                            "ActionBody" => "reply",
                                            'DefaultHeight' => false,
                                            "BgColor" => "#8176d6",
                                            "Text" => "<font color='#ffffff'>Отправить телефон</font>",
                                            "TextSize" => "large",
                                            //"OpenURLType"=>"external",
                                            //"OpenURLMediaType"=>"not-media"

                                        ],
                                        ['Silent' => true,
                                            "Columns" => 3,
                                            "Rows" => 1,
                                            "ActionType" => "reply",
                                            "ActionBody" => "#CallBackButton#0.1",
                                            'DefaultHeight' => false,
                                            "BgColor" => "#8176d6",
                                            "Text" => "<font color='#ffffff'>Отмена (в начало)</font>",
                                            "TextSize" => "large"

                                        ],


                                    ],

                                ];
                                break;

                            case'carousel':

                                $type_message = 'rich_media';

                                $rich_media =  [
                                    "Type"=>"rich_media",
                                    "BgColor"=>"#FFFFFF",
                                    "ButtonsGroupColumns"=>6,
                                    //"ButtonsGroupRows"=>7,
                                ];


                                foreach ($keyboards as $button) {

                                    $rich_media['Buttons'][] = [
                                        //"Columns"=>6,
                                        //"Rows"=>3,
                                        "ActionType"=>"reply",
                                        "TextHAlign"=> "left",
                                        "TextVAlign"=> "top",
                                        "TextSize"=> "small",
                                        "ActionBody"=>$button['callback'],
                                        "Text"=>$button['text'],
                                        "alt_text"=>'Обновите приложение Viber, чтобы увидеть сообщение',

                                    ] ;


                                }

                                break;

                            default:
                                $keyboard = [];
                                break;

                        }

    } else {


            $keyboards = JSON_DECODE($keyboards, true);
            $keyboard =  [
                'Type'=>"keyboard",
                "BgColor"=>"#ffffff",
                'Buttons'=>[],
                ];


            foreach ($keyboards as $data => $key) {

                $keyboard['Buttons'][] = [
                    'Silent' => false,
                    "Columns"=> 3,
                    "Rows"=> 1,
                    "ActionType"=>"reply",
                    "ActionBody"=>'#CallBackButton#'.$key,
                    'DefaultHeight'=>false,
                    "BgColor"=>"#8176d6",
                    "Text"=>"<font color='#ffffff'>$data</font>",
                    "TextSize"=>"regular"
                ];

            }
        }


        $post = [
           'receiver' => $userId,
            'sender' => [
                        'name' => BOTNAME,
                        ],
            "min_api_version"=>5,
            'type'=>$type_message,
        ];

        switch ($type_message) {

            case'text':
        $post['text'] = $message;
        $post['keyboard'] = $keyboard;
        break;
            case'rich_media':
                $post['rich_media'] = $rich_media;
                break;

        }


        $request = $kernel->curlPost($url,JSON_ENCODE($post),$headers);

        //$kernel->fnlog($request);

    }


}