<?php
/** Подключаем классы */


spl_autoload_register(function ($class_name) {

    if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.$class_name . '.class'.'.php')) {
        include __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class_name . '.class' . '.php';
    } else {throw new Exception('Нет файла вызываемого класса: '.$class_name);}

});

?>