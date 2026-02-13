<?php

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';

// $APPLICATION->SetTitle("Добавление в лог");
// require_once $_SERVER['DOCUMENT_ROOT'].'/local/classes/OTUSLogger.php';
// OTUSLogger::log("Открыта страница writelog.php");

# Запись в лог
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$logFile = $_SERVER['DOCUMENT_ROOT'].'/local/logs/log_custom.log';
file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] OTUS: Открыта страница writelog.php | IP: $ip\n", FILE_APPEND);

$APPLICATION->SetTitle("Добавление в лог");
?>
?>
<ul class="list-group">
    <li class="list-group-item">
        <a href="/local/logs/log_custom.log" target="_blank">Файл лога</a>,
        в лог добавлено 'Открыта страница writelog.php'
    </li>
</ul>

<?php require $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'; ?>