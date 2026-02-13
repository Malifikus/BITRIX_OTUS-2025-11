<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("OTUS ДЗ Отладка и логирование");

# Подключаем класс логгера
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/classes/OTUSLogger.php';

# Получаем IP адресс
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

# Пишем в лог
OTUSLogger::log("HTTP запрос от IP: $ip");

# Время для вывода на странице
$time = date('Y-m-d H:i:s');

# HTML визуализация
?>

<h1>ДЗ Отладка и логирование</h1>

<h3>Лог добавлен</h3>
<p><strong>Дата:</strong> <?= htmlspecialchars($time) ?></p>
<p><strong>IP:</strong> <?= htmlspecialchars($ip) ?></p>
<p><strong>Лог:</strong> <code>/otus.log</code></p>


<ul>
    <li><a href="/otus.log" target="_blank">Посмотреть лог</a></li>
    <li><a href="/otus/TestLogger.php" target="_blank">Ошибочный лог</a></li>
    <li><a href="/local/php_interface/clear_log.php">Очистить лог</a></li>
    <li><a href="/otus/clearexception.php">Очистить системный лог</a></li>
    <li><a href="/otus/writelog.php" target="_blank">Добавить запись в лог</a></li>
</ul>

<h3>Ручной лог:</h3>
<pre><?= htmlspecialchars(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/local/logs/log_custom.log') ?: 'пусто') ?></pre>

<h3>Системный лог:</h3>
<pre><?= htmlspecialchars(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/otus.log') ?: 'пусто') ?></pre>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>