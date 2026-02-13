<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
# Вызываем ошибку
$result = 1 / 0;
echo "Ошибка вызвана";