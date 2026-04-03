<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;

$eventManager = EventManager::getInstance();

// Регистрация обработчиков инфоблока
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ['IblockRequestsSync', 'onElementAfterAdd']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['IblockRequestsSync', 'onElementAfterUpdate']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementDelete', ['IblockRequestsSync', 'onElementBeforeDelete']);

class IblockRequestsSync
{
    private static $iblockCode = 'zayavki';
    private static $dealPropCode = 'SDELKA';
    private static $amountPropCode = 'SUMMA';
    private static $respPropCode = 'OTVETSTVENNYY';
    private static $syncInProgress = false;

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

    // Проверяет принадлежность элемента инфоблоку
    private static function isRequestsElement($arFields)
    {
        $iblockId = self::getIblockId();
        return $iblockId && isset($arFields['IBLOCK_ID']) && $arFields['IBLOCK_ID'] == $iblockId;
    }

    // Получает свойства элемента инфоблока
    private static function getElementProperties($elementId)
    {
        $props = [];
        $rs = \CIBlockElement::GetProperty(self::getIblockId() ?: 0, $elementId, ['sort' => 'asc'], ['ACTIVE' => 'Y']);
        while ($ar = $rs->Fetch()) {
            if (!isset($props[$ar['CODE']])) {
                $props[$ar['CODE']] = ['VALUE' => $ar['VALUE']];
            } elseif (!is_array($props[$ar['CODE']]['VALUE'])) {
                $props[$ar['CODE']]['VALUE'] = [$props[$ar['CODE']]['VALUE'], $ar['VALUE']];
            } else {
                $props[$ar['CODE']]['VALUE'][] = $ar['VALUE'];
            }
        }
        return $props;
    }

    // Получает ID сделки из свойства элемента
    private static function getDealIdFromElement($elementId)
    {
        $props = self::getElementProperties($elementId);
        $val = $props[self::$dealPropCode]['VALUE'] ?? null;
        if (is_array($val)) return (int)current($val);
        return $val ? (int)$val : null;
    }

    // Получает поля сделки из БД
    private static function getDealFields($dealId)
    {
        if (!Loader::includeModule('crm')) return null;
        $res = DealTable::getById($dealId);
        $deal = $res->fetch();
        if ($deal) {
            return [
                'ID' => $deal['ID'],
                'OPPORTUNITY' => $deal['OPPORTUNITY'],
                'ASSIGNED_BY_ID' => $deal['ASSIGNED_BY_ID'],
            ];
        }
        return null;
    }

    // Устанавливает флаг синхронизации
    public static function setSyncInProgress($value)
    {
        self::$syncInProgress = $value;
    }

    // Обработчик после добавления элемента
    public static function onElementAfterAdd(&$arFields)
    {
        if (!self::isRequestsElement($arFields)) return;
        self::syncDealFromIblock($arFields['ID'], $arFields);
    }

    // Обработчик после обновления элемента
    public static function onElementAfterUpdate(&$arFields)
    {
        if (!self::isRequestsElement($arFields)) return;
        // Пропускаем если синхронизация из сделки
        if (self::$syncInProgress) return;
        
        $props = self::getElementProperties($arFields['ID']);
        $amountRaw = $props[self::$amountPropCode]['VALUE'] ?? null;
        $newAmount = is_string($amountRaw) && strpos($amountRaw, '|') !== false 
            ? (float)explode('|', $amountRaw)[0] : (float)$amountRaw;
        $newRespId = $props[self::$respPropCode]['VALUE'] ?? null;
        if (is_array($newRespId)) $newRespId = (int)current($newRespId);
        
        $dealId = self::getDealIdFromElement($arFields['ID']);
        if (!$dealId) return;
        
        $dealFields = self::getDealFields($dealId);
        if (!$dealFields) return;
        
        $dealAmount = (float)($dealFields['OPPORTUNITY'] ?? 0);
        $dealRespId = (int)($dealFields['ASSIGNED_BY_ID'] ?? 0);
        
        // Синхронизируем только при изменении значений
        $amountChanged = abs($newAmount - $dealAmount) > 0.01;
        $respChanged = $newRespId != $dealRespId;
        
        if ($amountChanged || $respChanged) {
            self::syncDealFromIblock($arFields['ID'], $arFields);
        }
    }

    // Обработчик перед удалением элемента
    public static function onElementBeforeDelete($id)
    {
        return true;
    }

    // Синхронизирует сделку с данными инфоблока
    private static function syncDealFromIblock($elementId, $arFields)
    {
        $dealId = self::getDealIdFromElement($elementId);
        if (!$dealId) return;
        if (!Loader::includeModule('crm')) return;

        $props = self::getElementProperties($elementId);
        $amountRaw = $props[self::$amountPropCode]['VALUE'] ?? null;
        $amount = is_string($amountRaw) && strpos($amountRaw, '|') !== false 
            ? (float)explode('|', $amountRaw)[0] : (float)$amountRaw;
        $respId = $props[self::$respPropCode]['VALUE'] ?? null;
        if (is_array($respId)) $respId = (int)current($respId);

        $arDeal = [];
        if ($amount !== null && $amount > 0) $arDeal['OPPORTUNITY'] = $amount;
        if ($respId !== null && $respId > 0) $arDeal['ASSIGNED_BY_ID'] = $respId;

        if (!empty($arDeal)) {
            DealTable::update($dealId, $arDeal);
        }
    }
}
