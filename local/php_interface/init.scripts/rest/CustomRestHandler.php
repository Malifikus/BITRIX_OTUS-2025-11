<?php

// Регистрация обработчика событий
AddEventHandler('rest', 'OnRestServiceBuildDescription', 'RegisterRestMethods');

define('STORAGE_FILE', $_SERVER['DOCUMENT_ROOT'] . '/local/logs/storage.json');

// Методы
function RegisterRestMethods()
{
    return [
        'my_entity_scope' => [
            'my_entity.add' => [
                'callback' => 'RestAddItem',
                'options' => []
            ],
            'my_entity.get' => [
                'callback' => 'RestGetItem',
                'options' => []
            ],
            'my_entity.list' => [
                'callback' => 'RestListItem',
                'options' => []
            ],
            'my_entity.update' => [
                'callback' => 'RestUpdateItem',
                'options' => []
            ],
            'my_entity.delete' => [
                'callback' => 'RestDeleteItem',
                'options' => []
            ],
        ]
    ];
}

// Чтение всех записей
function GetStorage() {
    if (!file_exists(STORAGE_FILE)) {
        return [];
    }
    $json = file_get_contents(STORAGE_FILE);
    return json_decode($json, true) ?: [];
}

function SaveStorage($data) {
    $dir = dirname(STORAGE_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    file_put_contents(STORAGE_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// Создание новой записи
function RestAddItem($query, $nav, $server)
{
    WriteRestLog('add', $query);

    if (empty($query['NAME'])) {
        throw new \Bitrix\Rest\RestException('Field NAME is required');
    }

    $storage = GetStorage();
    
    // Генерация нового ID
    $ids = array_keys($storage);
    $newId = empty($ids) ? 1 : max($ids) + 1;

    // Сохранение данных
    $storage[$newId] = [
        'ID' => $newId,
        'NAME' => $query['NAME'],
        'DATE_CREATE' => date('Y-m-d H:i:s')
    ];

    SaveStorage($storage);

    WriteRestLog('add_ok', $storage[$newId]);
    return ['result' => $storage[$newId]];
}

// Получение конкретной записи по ID
function RestGetItem($query, $nav, $server)
{
    WriteRestLog('get', $query);

    if (empty($query['ID'])) {
        throw new \Bitrix\Rest\RestException('Field ID is required');
    }

    $id = intval($query['ID']);
    $storage = GetStorage();

    if (!isset($storage[$id])) {
        throw new \Bitrix\Rest\RestException('Item not found');
    }

    WriteRestLog('get_ok', $storage[$id]);
    return ['result' => $storage[$id]];
}

// Получение полного списка всех записей
function RestListItem($query, $nav, $server)
{
    WriteRestLog('list', $query);
    
    $storage = GetStorage();
    $items = array_values($storage);
    
    WriteRestLog('list_ok', ['count' => count($items)]);
    return ['result' => $items];
}

// Обновление существующей записи по ID
function RestUpdateItem($query, $nav, $server)
{
    WriteRestLog('update', $query);

    if (empty($query['ID'])) {
        throw new \Bitrix\Rest\RestException('Field ID is required');
    }

    $id = intval($query['ID']);
    $storage = GetStorage();

    if (!isset($storage[$id])) {
        throw new \Bitrix\Rest\RestException('Item not found');
    }

    // Обновление полей
    if (!empty($query['NAME'])) {
        $storage[$id]['NAME'] = $query['NAME'];
    }
    $storage[$id]['DATE_UPDATE'] = date('Y-m-d H:i:s');

    SaveStorage($storage);

    WriteRestLog('update_ok', $storage[$id]);
    return ['result' => $storage[$id]];
}

// Удаление записи из файла по ID
function RestDeleteItem($query, $nav, $server)
{
    WriteRestLog('delete', $query);

    if (empty($query['ID'])) {
        throw new \Bitrix\Rest\RestException('Field ID is required');
    }

    $id = intval($query['ID']);
    $storage = GetStorage();

    if (!isset($storage[$id])) {
        throw new \Bitrix\Rest\RestException('Item not found');
    }

    unset($storage[$id]);
    SaveStorage($storage);

    WriteRestLog('delete_ok', ['id' => $id, 'status' => 'ok']);
    return ['result' => ['deleted' => true]];
}

// Функция логирования
function WriteRestLog($action, $data)
{
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/local/logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $file = $dir . '/rest_dz12.log';
    $msg = date('[Y-m-d H:i:s] ') . $action . ': ' . json_encode($data) . PHP_EOL;
    file_put_contents($file, $msg, FILE_APPEND);
}
