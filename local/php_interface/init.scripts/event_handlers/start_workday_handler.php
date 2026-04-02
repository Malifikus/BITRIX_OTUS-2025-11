<?php

// namespace
namespace Otus;

use Bitrix\Main\Page\Asset;

// Подключаем класс
if (!class_exists('Otus\StartWorkdayHandler')) {

    // Класс-обработчик
    class StartWorkdayHandler
    {
        // Подключение JS-файла
        public static function includeJs()
        {
            // Публичная часть
            if (!defined('ADMIN_SECTION') || ADMIN_SECTION !== true) {
                Asset::getInstance()->addJs('/local/js/start_workday.js');
            }
        }
    }
}

\Otus\StartWorkdayHandler::includeJs();
