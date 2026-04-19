<?php
require_once(__DIR__ . '/crest.php');
error_reporting(0);
ini_set('display_errors', 0);

$log = __DIR__ . '/handler.log';

// Логируем вход
file_put_contents($log, date('[Y-m-d H:i:s] ') . "=== CALL ===\n", FILE_APPEND);
file_put_contents($log, "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Читаем данные из POST
$data = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);

// Получаем ID активности
$activityId = $data['data']['FIELDS']['ID'] ?? 0;
if (!$activityId) {
    echo "OK";
    exit;
}
file_put_contents($log, "Activity ID: {$activityId}\n", FILE_APPEND);

// Запрашиваем полные данные активности
$activity = CRest::call('crm.activity.get', ['id' => $activityId]);
$fields = $activity['result'] ?? [];
$ownerId = (int)($fields['OWNER_ID'] ?? 0);
$ownerType = $fields['OWNER_TYPE_ID'] ?? '';

file_put_contents($log, "Owner: ID={$ownerId}, Type={$ownerType}\n", FILE_APPEND);

// Обновляем только контакты
if (($ownerType === 'C' || $ownerType == 3) && $ownerId > 0) {
    $result = CRest::call('crm.contact.update', [
        'id' => $ownerId,
        'fields' => ['UF_LAST_COMMUNICATION' => date('Y-m-d H:i:s')]
    ]);
    file_put_contents($log, "Update: " . print_r($result, true) . "\n", FILE_APPEND);
}

// Одижание ответа от приложения
echo "OK";
exit;
