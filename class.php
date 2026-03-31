<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule('currency')) {
    ShowError("Модуль валют не установлен");
    return;
}

class OtusCurrencySelector extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $currencyCode = $arParams["CURRENCY_CODE"];
        $arCur = \CCurrency::GetByID($currencyCode);
        
        if (!$arCur) {
            $arParams["CURRENCY_CODE"] = "USD"; // Сброс на дефолт
        }

        return $arParams;
    }

    private function getCurrencyRate($code)
    {
        return \CCurrency::GetByID($code); // Получаем данные
    }

    public function executeComponent()
    {
        try {
            $currencyCode = $this->arParams["CURRENCY_CODE"];
            $arData = $this->getCurrencyRate($currencyCode);
            
            $this->arResult["RATE_TEXT"] = "";
            $this->arResult["CURRENCY_NAME"] = "";

            if ($arData) {
                // Формируем строку курса
                $this->arResult["RATE_TEXT"] = $arData["AMOUNT_CNT"] . " " . $arData["CURRENCY"] . " = " . $arData["AMOUNT"] . " RUB";
                $this->arResult["CURRENCY_NAME"] = $arData["NAME"];
            }

            $this->IncludeComponentTemplate(); // Подключаем шаблон
        }
        catch (\Exception $e) {
            ShowError($e->getMessage());
        }
    }
}