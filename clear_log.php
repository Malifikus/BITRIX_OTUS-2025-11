<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";

file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/logs/log_custom.log', '');
header('Location: /otus/debug.php');
exit;