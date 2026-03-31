<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (\Bitrix\Main\Loader::includeModule('currency')) {
    $arCurrencies = [];
    $rsCurrencies = \CCurrency::GetList($by = "SORT", $order = "ASC");
    
    while ($arCur = $rsCurrencies->Fetch()) {
        $arCurrencies[$arCur["CURRENCY"]] = $arCur["NAME"] . " (" . $arCur["CURRENCY"] . ")";
    }
} else {
    $arCurrencies = ["USD" => "Доллар США (USD)"];
}

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "CURRENCY_CODE" => [
            "PARENT" => "BASE",
            "NAME" => "Валюта по умолчанию",
            "TYPE" => "LIST",
            "VALUES" => $arCurrencies,
            "DEFAULT" => "USD",
        ],
    ],
];