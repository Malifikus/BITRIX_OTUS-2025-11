<?php

// Регистрируем событие rest
AddEventHandler('rest', 'OnRestServiceBuildDescription', 'RegisterRestMethods');

// Список методов
function RegisterRestMethods()
{
    return [
        // Имя своего скоупа
        'my_entity_scope' => [
            // Создание новой записи
            'my_entity.add' => [
                'callback' => 'RestAddItem',
                'options' => []
            ],
            // Получение записи по ид
            'my_entity.get' => [
                'callback' => 'RestGetItem',
                'options' => []
            ],
            // Обновление существующей записи
            'my_entity.update' => [
                'callback' => 'RestUpdateItem',
                'options' => []
            ],
            // Удаление записи по ид
            'my_entity.delete' => [
                'callback' => 'RestDeleteItem',
                'options' => []
            ],
        ]
    ];
}

// Обработчик добавления записи
function RestAddItem($query, $nav, $server)
{
    // Логируем входящий запрос
    WriteRestLog('add', $query);

    // Проверяем поле имя
    if (empty($query['NAME'])) {
        throw new \Bitrix\Rest\RestException('field NAME required');
    }

    // Эмулируем сохранение записи
    $id = rand(1000, 9999);

    // Логируем результат операции
    WriteRestLog('add_ok', ['id' => $id]);

    return ['result' => ['id' => $id]];
}

// Обработчик получения записи
function RestGetItem($query, $nav, $server)
{
    // Логируем входящий запрос
    WriteRestLog('get', $query);

    // Проверяем поле идентификатор
    if (empty($query['ID'])) {
        throw new \Bitrix\Rest\RestException('field ID required');
    }

    // Эмулируем данные записи
    $item = [
        'ID' => $query['ID'],
        'NAME' => 'Item ' . $query['ID'],
        'DATE' => date('Y-m-d H:i:s')
    ];

    // Логируем результат операции
    WriteRestLog('get_ok', $item);

    return ['result' => $item];
}

// Обработчик обновления записи
function RestUpdateItem($query, $nav, $server)
{
    // Логируем входящий запрос
    WriteRestLog('update', $query);

    // Проверяем поле идентификатор
    if (empty($query['ID'])) {
        throw new \Bitrix\Rest\RestException('field ID required');
    }

    // Логируем результат операции
    WriteRestLog('update_ok', ['status' => 'ok']);

    return ['result' => ['updated' => true]];
}

// Обработчик удаления записи
function RestDeleteItem($query, $nav, $server)
{
    // Логируем входящий запрос
    WriteRestLog('delete', $query);

    // Проверяем поле идентификатор
    if (empty($query['ID'])) {
        throw new \Bitrix\Rest\RestException('field ID required');
    }

    // Ллогируем результат
    WriteRestLog('delete_ok', ['status' => 'ok']);

    return ['result' => ['deleted' => true]];
}

// Записываем данные в лог
function WriteRestLog($action, $data)
{
    // Путь к папке логов
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/local/logs';

    // Создаем папку лога
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    // Путь к файлу лога
    $file = $dir . '/rest_dz12.log';

    // Формируем строку сообщения
    $msg = date('[Y-m-d H:i:s] ') . $action . ': ' . json_encode($data) . PHP_EOL;

    // Ддобавляем запись в файл
    file_put_contents($file, $msg, FILE_APPEND);
}
