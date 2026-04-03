<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

$eventManager = EventManager::getInstance();

// Регистрация обработчиков сделок
$eventManager->addEventHandler('crm', 'OnAfterCrmDealAdd', ['CrmDealSync', 'onDealAfterAdd']);
$eventManager->addEventHandler('crm', 'OnAfterCrmDealUpdate', ['CrmDealSync', 'onDealAfterUpdate']);
$eventManager->addEventHandler('crm', 'OnBeforeCrmDealDelete', ['CrmDealSync', 'onDealBeforeDelete']);

class CrmDealSync
{
    private static $iblockCode = 'zayavki';
    private static $dealPropCode = 'SDELKA';
    private static $amountPropCode = 'SUMMA';
    private static $respPropCode = 'OTVETSTVENNYY';

    // Получает ID инфоблока по коду
    private static function getIblockId()
    {
        static $id = null;
        if ($id !== null) return $id;
        if (!Loader::includeModule('iblock')) return null;
        $rs = \CIBlock::GetList([], ['CODE' => self::$iblockCode]);
        if ($ar = $rs->Fetch()) $id = (int)$ar['ID'];
        return $id;
    }

    // Получает свойства элемента инфоблока
    private static function getIblockElementProperties($elementId)
    {
        $props = [];
        $rs = \CIBlockElement::GetProperty(self::getIblockId() ?: 0, $elementId, ['sort' => 'asc'], ['ACTIVE' => 'Y']);
        while ($ar = $rs->Fetch()) {
            if (!isset($props[$ar['CODE']])) {
                $props[$ar['CODE']] = $ar['VALUE'];
            } elseif (!is_array($props[$ar['CODE']])) {
                $props[$ar['CODE']] = [$props[$ar['CODE']], $ar['VALUE']];
            } else {
                $props[$ar['CODE']][] = $ar['VALUE'];
            }
        }
        return $props;
    }

    // Обработчик после добавления сделки
    public static function onDealAfterAdd(&$arFields)
    {
        if (!isset($arFields['ID']) || !$arFields['ID']) return;
        self::syncIblockFromDeal($arFields['ID'], $arFields);
    }

    // Обработчик после обновления сделки
    public static function onDealAfterUpdate(&$arFields)
    {
        if (!isset($arFields['ID']) || !$arFields['ID']) return;
        self::syncIblockFromDeal($arFields['ID'], $arFields);
    }

    // Обработчик перед удалением сделки
    public static function onDealBeforeDelete($id)
    {
        return true;
    }

    // Синхронизирует инфоблок с данными сделки
    private static function syncIblockFromDeal($dealId, $arDealFields)
    {
        if (!Loader::includeModule('iblock')) return;
        $iblockId = self::getIblockId();
        if (!$iblockId) return;

        $arFilter = ['IBLOCK_ID' => $iblockId, 'PROPERTY_' . self::$dealPropCode => $dealId];
        $rs = \CIBlockElement::GetList(['ID' => 'DESC'], $arFilter, false, false, ['ID']);
        
        while ($ar = $rs->Fetch()) {
            $elementId = (int)$ar['ID'];
            $currentProps = self::getIblockElementProperties($elementId);
            
            // Формируем массив обновления свойств
            $arUpdate = [];
            $arUpdate['PROPERTY_VALUES'] = [];
            // Сохраняем привязку к сделке
            $arUpdate['PROPERTY_VALUES'][self::$dealPropCode] = $currentProps[self::$dealPropCode] ?? $dealId;
            
            // Обновляем сумму (формат Деньги)
            if (isset($arDealFields['OPPORTUNITY']) && $arDealFields['OPPORTUNITY'] !== '') {
                $arUpdate['PROPERTY_VALUES'][self::$amountPropCode] = [
                    'VALUE' => $arDealFields['OPPORTUNITY'],
                    'CURRENCY' => 'RUB'
                ];
            }
            // Обновляем ответственного
            if (isset($arDealFields['ASSIGNED_BY_ID']) && $arDealFields['ASSIGNED_BY_ID'] > 0) {
                $arUpdate['PROPERTY_VALUES'][self::$respPropCode] = $arDealFields['ASSIGNED_BY_ID'];
            }

            if (!empty($arUpdate['PROPERTY_VALUES'])) {
                // Ставим флаг чтобы избежать цикла
                \IblockRequestsSync::setSyncInProgress(true);
                $el = new \CIBlockElement();
                $el->Update($elementId, $arUpdate);
                \IblockRequestsSync::setSyncInProgress(false);
            }
        }
    }
}
