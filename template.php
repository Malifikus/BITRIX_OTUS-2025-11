<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<?php if (!empty($arResult["RATE_TEXT"])): ?>
Валюта: <?= $arResult["CURRENCY_NAME"] ?>
Курс: <?= $arResult["RATE_TEXT"] ?>
<?php else: ?>
Курс не найден.
<?php endif; ?>